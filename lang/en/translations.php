<?php

return [
    'navigation' => 'Translations',
    'singular' => 'translation',
    'plural' => 'Translations',

    'table' => [
        'locale' => 'Language',
        'group' => 'File',
        'key' => 'Key',
        'value' => 'Text on the site',
        'default' => 'Default value',
    ],

    'form' => [
        'locale' => 'Language',
        'group' => 'File',
        'group_helper' => 'File name under lang/, e.g. "front". An asterisk (*) marks JSON translations.',
        'key' => 'Key',
        'key_helper' => 'Path inside the file, e.g. "nav.home" or "landing.advert.title".',
        'value' => 'Text on the site',
        'value_helper' => 'What the visitor sees. Leave empty to fall back to the file value.',
        'default' => 'Default value from the file',
    ],

    'filter' => [
        'overridden' => 'Edited only',
        'untranslated' => 'Untranslated',
    ],

    'actions' => [
        'reset' => 'Reset to default',
        'all_locales' => 'Translate into all languages',
        'all_locales_saved' => 'Saved in all languages',
        'reset_selected' => 'Reset selected to default',
        'import' => 'Load new strings',
        'import_description' => 'Loads strings from the lang/ files. Anything you have edited here is kept.',
        'import_done' => 'Strings loaded',
        'flush' => 'Clear cache',
        'flush_done' => 'Translation cache cleared',
    ],
];
