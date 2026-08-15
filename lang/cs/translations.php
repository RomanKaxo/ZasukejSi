<?php

return [
    'navigation' => 'Překlady',
    'singular' => 'překlad',
    'plural' => 'Překlady',

    'table' => [
        'locale' => 'Jazyk',
        'group' => 'Soubor',
        'key' => 'Klíč',
        'value' => 'Text na webu',
        'default' => 'Výchozí hodnota',
    ],

    'form' => [
        'locale' => 'Jazyk',
        'group' => 'Soubor',
        'group_helper' => 'Název souboru v lang/, například „front". Hvězdička (*) označuje JSON překlady.',
        'key' => 'Klíč',
        'key_helper' => 'Cesta uvnitř souboru, například „nav.home" nebo „landing.advert.title".',
        'value' => 'Text na webu',
        'value_helper' => 'Co uvidí návštěvník. Prázdné pole znamená, že se použije výchozí hodnota ze souboru.',
        'default' => 'Výchozí hodnota ze souboru',
    ],

    'filter' => [
        'overridden' => 'Jen upravené',
        'untranslated' => 'Nepřeložené',
    ],

    'actions' => [
        'reset' => 'Vrátit výchozí',
        'all_locales' => 'Přeložit do všech jazyků',
        'all_locales_saved' => 'Uloženo ve všech jazycích',
        'reset_selected' => 'Vrátit výchozí u vybraných',
        'import' => 'Načíst nové texty',
        'import_description' => 'Načte řetězce ze souborů lang/. Texty, které jste zde upravili, zůstanou zachované.',
        'import_done' => 'Texty načteny',
        'flush' => 'Vyprázdnit cache',
        'flush_done' => 'Cache překladů vyprázdněna',
    ],
];
