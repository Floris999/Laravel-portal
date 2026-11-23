<?php

return [
    'index' => [
        'title' => 'Leveranciers',
        'heading' => 'Leveranciers',
        'subheading' => 'Overzicht van alle leveranciers.',
        'add' => '+ Toevoegen',
        'empty' => 'Nog geen leveranciers toegevoegd.',
        'no_results' => 'Geen leveranciers gevonden.',
        'search' => 'Zoeken',
        'search_placeholder' => 'Bedrijfsnaam, e-mailadres of plaats',
    ],

    'create' => [
        'title' => 'Leverancier toevoegen',
        'heading' => 'Leverancier toevoegen',
        'subheading' => 'Vul de gegevens van de nieuwe leverancier in.',
        'submit' => 'Leverancier aanmaken',
    ],

    'edit' => [
        'title' => 'Leverancier wijzigen',
        'heading' => 'Leverancier wijzigen',
        'subheading' => 'Wijzig de gegevens van :name.',
        'submit' => 'Wijzigingen opslaan',
        'archive' => 'Leverancier archiveren',
        'archive_confirm' => 'Weet je zeker dat je :name wilt archiveren? De leverancier verdwijnt dan uit het actieve overzicht, maar blijft bewaard.',
    ],

    'show' => [
        'title' => 'Leveranciergegevens',
        'heading' => 'Leveranciergegevens',
        'edit' => 'Leverancier wijzigen',
        'back' => 'Terug naar leveranciers',
        'company_details' => 'Bedrijfsgegevens',
        'contact_details' => 'Contactgegevens',
        'address_details' => 'Adresgegevens',
        'not_provided' => 'Niet opgegeven',
    ],

    'archived' => [
        'title' => 'Gearchiveerde leveranciers',
        'heading' => 'Gearchiveerde leveranciers',
        'subheading' => 'Overzicht van alle gearchiveerde leveranciers.',
        'empty' => 'Geen gearchiveerde leveranciers.',
        'restore' => 'Dearchiveren',
        'restore_confirm' => 'Weet je zeker dat je :name wilt dearchiveren? De leverancier verschijnt dan weer tussen de actieve leveranciers.',
    ],

    'cancel' => 'Annuleren',

    'table' => [
        'id' => 'ID',
        'name' => 'Bedrijfsnaam',
        'kvk' => 'KVK',
        'email' => 'E-mailadres',
        'phone' => 'Telefoon',
        'city' => 'Plaats',
        'supplier_since' => 'Leverancier sinds',
        'status' => 'Status',
        'active' => 'Actief',
        'inactive' => 'Inactief',
        'action' => 'Actie',
        'copy' => 'Kopiëren',
        'copy_success' => 'Leveranciergegevens gekopieerd.',
        'copy_error' => 'Kopiëren is niet gelukt. Controleer de browserrechten.',
    ],

    'fields' => [
        'name' => 'Bedrijfsnaam',
        'name_placeholder' => 'Bedrijfsnaam B.V.',
        'kvk' => 'KVK-nummer',
        'kvk_placeholder' => '12345678',
        'vat_number' => 'BTW-nummer',
        'vat_number_placeholder' => 'NL123456789B01',
        'admin_email' => 'Administratie e-mailadres',
        'admin_email_placeholder' => 'administratie@bedrijf.nl',
        'email' => 'Factuur e-mailadres',
        'email_placeholder' => 'info@bedrijf.nl',
        'phone' => 'Algemeen telefoonnummer',
        'phone_placeholder' => '0612345678',
        'supplier_since' => 'Leverancier sinds',
        'address' => 'Adres',
        'address_placeholder' => 'Straat 10',
        'postal_code' => 'Postcode',
        'postal_code_placeholder' => '1234 AB',
        'city' => 'Plaats',
        'city_placeholder' => 'Plaats',
        'country' => 'Land',
        'status' => 'Status',
        'logo' => 'Profielfoto',
    ],

    'countries' => [
        'Nederland',
        'België',
        'Duitsland',
        'Overig',
    ],
];
