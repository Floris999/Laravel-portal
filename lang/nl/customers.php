<?php

return [
    'index' => [
        'title' => 'Klanten',
        'heading' => 'Klanten',
        'subheading' => 'Overzicht van alle klanten.',
        'add' => '+ Toevoegen',
        'empty' => 'Nog geen klanten toegevoegd.',
        'no_results' => 'Geen klanten gevonden.',
        'search' => 'Zoeken',
        'search_placeholder' => 'Bedrijfsnaam, e-mailadres of plaats',
    ],

    'create' => [
        'title' => 'Klant toevoegen',
        'heading' => 'Klant toevoegen',
        'subheading' => 'Vul de gegevens van de nieuwe klant in.',
        'submit' => 'Klant aanmaken',
    ],

    'edit' => [
        'title' => 'Klant wijzigen',
        'heading' => 'Klant wijzigen',
        'subheading' => 'Wijzig de gegevens van :name.',
        'submit' => 'Wijzigingen opslaan',
        'archive' => 'Klant archiveren',
        'archive_confirm' => 'Weet je zeker dat je :name wilt archiveren? De klant verdwijnt dan uit het actieve overzicht, maar blijft bewaard.',
    ],

    'show' => [
        'title' => 'Klantgegevens',
        'heading' => 'Klantgegevens',
        'edit' => 'Klant wijzigen',
        'back' => 'Terug naar klanten',
        'company_details' => 'Bedrijfsgegevens',
        'contact_details' => 'Contactgegevens',
        'address_details' => 'Adresgegevens',
        'not_provided' => 'Niet opgegeven',
    ],

    'archived' => [
        'title' => 'Gearchiveerde klanten',
        'heading' => 'Gearchiveerde klanten',
        'subheading' => 'Overzicht van alle gearchiveerde klanten.',
        'empty' => 'Geen gearchiveerde klanten.',
        'restore' => 'Dearchiveren',
        'restore_confirm' => 'Weet je zeker dat je :name wilt dearchiveren? De klant verschijnt dan weer tussen de actieve klanten.',
    ],

    'cancel' => 'Annuleren',

    'table' => [
        'id' => 'ID',
        'name' => 'Bedrijfsnaam',
        'kvk' => 'KVK',
        'email' => 'E-mailadres',
        'phone' => 'Telefoon',
        'city' => 'Plaats',
        'customer_since' => 'Klant sinds',
        'status' => 'Status',
        'active' => 'Actief',
        'inactive' => 'Inactief',
        'action' => 'Actie',
        'copy' => 'Kopiëren',
        'copy_success' => 'Klantgegevens gekopieerd.',
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
        'customer_since' => 'Klant sinds',
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
