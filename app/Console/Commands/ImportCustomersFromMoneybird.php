<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

#[Signature('moneybird:import-customers {--dry-run : Toon wat er geimporteerd zou worden zonder iets op te slaan} {--force : Sla de bevestigingsvraag over}')]
#[Description('Importeer alle Moneybird-contacten in het portaal: klanten als klant, contacten met een L-nummer als leverancier (eenmalige initiele sync)')]
class ImportCustomersFromMoneybird extends Command
{
    private const CONTACTS_PER_PAGE = 100;

    private const KVK_MAX_LENGTH = 20;

    private const VAT_NUMBER_MAX_LENGTH = 20;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        try {
            [$request, $administrationId] = $this->connection();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if (! $isDryRun && ! $this->option('force') && ! $this->confirmImport($administrationId)) {
            $this->info('Geannuleerd.');

            return self::SUCCESS;
        }

        try {
            $contacts = $this->fetchContacts($request, $administrationId);
        } catch (Throwable $exception) {
            $this->error("Contacten ophalen uit Moneybird mislukt: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $this->info(($isDryRun ? '[dry-run] ' : '')."{$contacts->count()} contacten gevonden in Moneybird (administratie {$administrationId}).");

        $outcome = $this->importContacts($contacts, $isDryRun);

        $this->report($outcome, $isDryRun);

        return $outcome['failed'] === [] ? self::SUCCESS : self::FAILURE;
    }

    private function confirmImport(string $administrationId): bool
    {
        $existingCustomers = Customer::withTrashed()->count();
        $existingSuppliers = Supplier::withTrashed()->count();

        $this->warn("Alle contacten van Moneybird-administratie {$administrationId} worden in het portaal gezet (contacten met een L-nummer als leverancier, de rest als klant).");

        if ($existingCustomers > 0 || $existingSuppliers > 0) {
            $this->warn("Let op: het portaal bevat al {$existingCustomers} klanten en {$existingSuppliers} leveranciers. Bestaande koppelingen worden overgeslagen.");
        }

        return $this->confirm('Doorgaan?', false);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $contacts
     * @return array{created: array<class-string<Customer|Supplier>, int>, skipped: array<string, array<int, string>>, failed: array<int, string>, warnings: array<int, string>}
     */
    private function importContacts(Collection $contacts, bool $isDryRun): array
    {
        $knownContactIds = Customer::withTrashed()->whereNotNull('moneybird_contact_id')->pluck('moneybird_contact_id')
            ->merge(Supplier::withTrashed()->whereNotNull('moneybird_contact_id')->pluck('moneybird_contact_id'))
            ->flip();

        $takenIds = [
            Customer::class => Customer::withTrashed()->pluck('id')->flip()->all(),
            Supplier::class => Supplier::withTrashed()->pluck('id')->flip()->all(),
        ];

        $outcome = [
            'created' => [Customer::class => 0, Supplier::class => 0],
            'skipped' => [],
            'failed' => [],
            'warnings' => [],
        ];

        $import = function (array $contact) use ($isDryRun, $knownContactIds, &$takenIds, &$outcome): void {
            $contactId = (string) $contact['id'];
            $label = $this->contactLabel($contact);
            $name = $this->contactName($contact);

            if ($knownContactIds->has($contactId)) {
                $outcome['skipped']['al geimporteerd'][] = $label;

                return;
            }

            if ($name === null) {
                $outcome['skipped']['geen bedrijfs- of persoonsnaam'][] = $label;

                return;
            }

            $modelClass = $this->modelClassFor($contact);
            $preferredId = $this->preferredId($contact);
            $id = $this->availableId($preferredId, $takenIds[$modelClass]);

            try {
                [$attributes, $warnings] = $this->attributes($contact, $name, $modelClass);

                if (! $isDryRun) {
                    $this->store($modelClass, $attributes, $id);
                }
            } catch (Throwable $exception) {
                $outcome['failed'][] = "{$label}: {$exception->getMessage()}";

                return;
            }

            $outcome['created'][$modelClass]++;

            if ($preferredId !== null && $id === null) {
                $warnings[] = "nummer {$contact['customer_id']} was al in gebruik, er is een nieuw nummer toegekend";
            }

            foreach ($warnings as $warning) {
                $outcome['warnings'][] = "{$label}: {$warning}";
            }

            if ($isDryRun) {
                $type = $modelClass === Supplier::class ? 'leverancier' : 'klant';
                $this->line("[dry-run] {$label} -> {$type}".($id === null ? '' : " #{$id}"));
            }
        };

        $orderedContacts = $contacts
            ->sortBy(fn (array $contact) => $this->preferredId($contact) === null ? 1 : 0)
            ->values();

        if ($isDryRun) {
            $orderedContacts->each($import);
        } else {
            $this->withProgressBar($orderedContacts, $import);
            $this->newLine(2);
        }

        return $outcome;
    }

    /**
     * Fetch every contact, following Moneybird's pagination.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function fetchContacts(PendingRequest $request, string $administrationId): Collection
    {
        $contacts = [];
        $page = 1;

        do {
            $pageContacts = $request->get("{$administrationId}/contacts.json", [
                'per_page' => self::CONTACTS_PER_PAGE,
                'page' => $page,
            ])->throw()->json();

            if (! is_array($pageContacts)) {
                throw new RuntimeException('Moneybird returned an unexpected contacts response.');
            }

            $contacts = array_merge($contacts, $pageContacts);
            $page++;
        } while (count($pageContacts) === self::CONTACTS_PER_PAGE);

        return collect($contacts);
    }

    /**
     * Keep Moneybird's customer number as the portal ID, because the outgoing
     * sync derives the Moneybird customer number from the portal ID (a plain
     * number for customers, an L-prefixed number for suppliers).
     * Returns null when the database should assign the next ID itself.
     *
     * @param  array<int|string, mixed>  $takenIds
     */
    private function availableId(?int $preferredId, array &$takenIds): ?int
    {
        if ($preferredId === null || isset($takenIds[$preferredId])) {
            return null;
        }

        $takenIds[$preferredId] = true;

        return $preferredId;
    }

    /**
     * @param  array<string, mixed>  $contact
     */
    private function preferredId(array $contact): ?int
    {
        $customerId = trim((string) ($contact['customer_id'] ?? ''));

        if ($this->isSupplierNumber($customerId)) {
            $customerId = substr($customerId, strlen(Supplier::NUMBER_PREFIX));
        }

        if (! ctype_digit($customerId) || strlen($customerId) > 18 || (int) $customerId < 1) {
            return null;
        }

        return (int) $customerId;
    }

    private function isSupplierNumber(mixed $customerId): bool
    {
        return is_string($customerId)
            && preg_match('/^'.preg_quote(Supplier::NUMBER_PREFIX, '/').'\d+$/i', trim($customerId)) === 1;
    }

    /**
     * @param  array<string, mixed>  $contact
     * @return class-string<Customer|Supplier>
     */
    private function modelClassFor(array $contact): string
    {
        return $this->isSupplierNumber($contact['customer_id'] ?? null) ? Supplier::class : Customer::class;
    }

    /**
     * @param  class-string<Customer|Supplier>  $modelClass
     * @param  array<string, mixed>  $attributes
     */
    private function store(string $modelClass, array $attributes, ?int $id): void
    {
        $record = new $modelClass($attributes);

        if ($id !== null) {
            $record->id = $id;
        }

        $record->saveQuietly();
    }

    /**
     * @param  array<string, mixed>  $contact
     * @param  class-string<Customer|Supplier>  $modelClass
     * @return array{0: array<string, mixed>, 1: array<int, string>}
     */
    private function attributes(array $contact, string $name, string $modelClass): array
    {
        $warnings = [];
        $isSupplier = $modelClass === Supplier::class;

        $generalEmail = $this->filled($contact['email'] ?? null);
        $invoiceEmail = $this->filled($contact['send_invoices_to_email'] ?? null);

        if (($invoiceEmail ?? $generalEmail) === null) {
            $warnings[] = 'geen e-mailadres in Moneybird';
        }

        $attributes = [
            'name' => $name,
            'kvk' => $this->limited($contact['chamber_of_commerce'] ?? null, self::KVK_MAX_LENGTH, 'KvK-nummer', $warnings),
            'vat_number' => $this->limited($contact['tax_number'] ?? null, self::VAT_NUMBER_MAX_LENGTH, 'btw-nummer', $warnings),
            'administration_email' => $generalEmail ?? $invoiceEmail ?? ($isSupplier ? '' : null),
            'email' => $invoiceEmail ?? $generalEmail ?? '',
            'phone' => $this->filled($contact['phone'] ?? null),
            ($isSupplier ? 'supplier_since' : 'customer_since') => Carbon::parse($contact['created_at'] ?? 'now')->toDateString(),
            'address' => $this->filled($contact['address1'] ?? null) ?? '',
            'postal_code' => $this->filled($contact['zipcode'] ?? null),
            'city' => $this->filled($contact['city'] ?? null) ?? '',
            'country' => $this->localCountry($contact['country'] ?? null),
            'active' => true,
            'moneybird_contact_id' => (string) $contact['id'],
        ];

        return [$attributes, $warnings];
    }

    /**
     * @param  array<int, string>  $warnings
     */
    private function limited(mixed $value, int $maxLength, string $field, array &$warnings): ?string
    {
        $value = $this->filled($value);

        if ($value !== null && mb_strlen($value) > $maxLength) {
            $warnings[] = "{$field} '{$value}' is langer dan {$maxLength} tekens en niet overgenomen";

            return null;
        }

        return $value;
    }

    private function filled(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $contact
     */
    private function contactName(array $contact): ?string
    {
        $companyName = $this->filled($contact['company_name'] ?? null);

        if ($companyName !== null) {
            return $companyName;
        }

        $personName = trim(($contact['firstname'] ?? '').' '.($contact['lastname'] ?? ''));

        return $personName === '' ? null : $personName;
    }

    /**
     * @param  array<string, mixed>  $contact
     */
    private function contactLabel(array $contact): string
    {
        return ($this->contactName($contact) ?? '(zonder naam)')." (Moneybird {$contact['id']})";
    }

    private function localCountry(mixed $countryCode): string
    {
        return match (is_string($countryCode) ? strtoupper($countryCode) : null) {
            'NL' => 'Nederland',
            'BE' => 'België',
            'DE' => 'Duitsland',
            default => 'Overig',
        };
    }

    /**
     * @param  array{created: array<class-string<Customer|Supplier>, int>, skipped: array<string, array<int, string>>, failed: array<int, string>, warnings: array<int, string>}  $outcome
     */
    private function report(array $outcome, bool $isDryRun): void
    {
        $this->info(($isDryRun ? '[dry-run] Zou aanmaken: ' : 'Aangemaakt: ')
            ."{$outcome['created'][Customer::class]} klanten, {$outcome['created'][Supplier::class]} leveranciers");

        foreach ($outcome['skipped'] as $reason => $labels) {
            $this->line('Overgeslagen ('.$reason.'): '.count($labels));

            if ($reason !== 'al geimporteerd') {
                foreach ($labels as $label) {
                    $this->line("  - {$label}");
                }
            }
        }

        if ($outcome['warnings'] !== []) {
            $this->warn('Waarschuwingen ('.count($outcome['warnings']).'):');

            foreach ($outcome['warnings'] as $warning) {
                $this->line("  - {$warning}");
            }
        }

        if ($outcome['failed'] !== []) {
            $this->error('Mislukt ('.count($outcome['failed']).'):');

            foreach ($outcome['failed'] as $failure) {
                $this->line("  - {$failure}");
            }
        }

        if (! $isDryRun && $outcome['created'][Customer::class] > 0) {
            $this->line('Tip: draai daarna `php artisan app:geocode-customers` om de coordinaten van de nieuwe klanten op te halen.');
        }
    }

    /**
     * @return array{0: PendingRequest, 1: string}
     */
    private function connection(): array
    {
        $apiToken = config('services.moneybird.api_token');
        $administrationId = config('services.moneybird.administration_id');
        $baseUrl = config('services.moneybird.base_url');

        if (! is_string($apiToken) || $apiToken === '' || ! is_string($administrationId) || $administrationId === '') {
            throw new RuntimeException('Moneybird API token and administration ID must be configured.');
        }

        if (! is_string($baseUrl) || $baseUrl === '') {
            throw new RuntimeException('Moneybird API base URL must be configured.');
        }

        $request = Http::baseUrl(rtrim($baseUrl, '/'))
            ->acceptJson()
            ->withToken($apiToken)
            ->timeout(30);

        return [$request, $administrationId];
    }
}
