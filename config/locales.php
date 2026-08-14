<?php

/**
 * Locales the public site serves.
 *
 * This list used to be repeated as a literal `['en', 'cs']` in five places
 * (SetLocale middleware, AppServiceProvider's language switch,
 * CountryStatsService, and twice in AuditTranslations), so adding a language
 * meant finding all of them. Everything now reads from here.
 *
 * `deepl` is the DeepL target-language code used by `translations:sync`; it
 * differs from the locale key for some languages (e.g. English needs a
 * regional variant).
 */
return [

    'supported' => [
        'cs' => [
            'name' => 'Česky',
            'native' => 'Česky',
            'flag' => 'flags/cs.png',
            'deepl' => 'CS',
            'audit' => true,
        ],
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => 'flags/en.png',
            'deepl' => 'EN-GB',
            'audit' => true,
        ],
        'ru' => [
            'name' => 'Russian',
            'native' => 'Русский',
            'flag' => 'flags/ru.png',
            'deepl' => 'RU',
            // Partial translation: the visible pages are covered, the rest
            // falls back. Flip to true once it is complete, and
            // `translations:audit` will start enforcing it.
            'audit' => false,
        ],
    ],

    /**
     * The locale translation files are authored in. Everything else is
     * generated from it by `php artisan translations:sync --locale=…`.
     */
    'source' => 'cs',

];
