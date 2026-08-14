<?php

namespace App\Support;

/**
 * Single source of truth for which locales the site serves.
 *
 * Reads config/locales.php so adding a language is one config edit rather than
 * hunting down five hardcoded `['en', 'cs']` literals.
 */
class Locales
{
    /**
     * Locale codes, in the order they should be offered to the user.
     *
     * @return array<int, string>
     */
    public static function codes(): array
    {
        return array_keys(config('locales.supported', []));
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function all(): array
    {
        return config('locales.supported', []);
    }

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && array_key_exists($locale, self::all());
    }

    /**
     * The locale translation files are authored in.
     */
    public static function source(): string
    {
        return config('locales.source', 'cs');
    }

    /**
     * Locales that are generated from the source locale.
     *
     * @return array<int, string>
     */
    public static function targets(): array
    {
        return array_values(array_diff(self::codes(), [self::source()]));
    }

    /**
     * Locales that actually have translation files on disk.
     *
     * A locale can be switched on in config before its files exist — Laravel
     * then falls back to `app.fallback_locale`, so the site still works.
     *
     * @return array<int, string>
     */
    public static function withTranslations(): array
    {
        return array_values(array_filter(
            self::codes(),
            fn (string $locale) => is_dir(lang_path($locale))
        ));
    }

    /**
     * Locales `translations:audit` requires to be complete.
     *
     * A partial translation (`audit => false`) is a legitimate state — the
     * visible pages are covered and the rest falls back — so it must not make
     * the audit report every key in the project as missing.
     *
     * @return array<int, string>
     */
    public static function audited(): array
    {
        return array_values(array_filter(
            self::withTranslations(),
            fn (string $locale) => (bool) (self::all()[$locale]['audit'] ?? true)
        ));
    }

    /**
     * Name shown in a language switcher, in the language itself.
     */
    public static function nativeName(string $locale): string
    {
        return self::all()[$locale]['native'] ?? strtoupper($locale);
    }

    /**
     * Public path of the flag asset for a locale.
     */
    public static function flag(string $locale): string
    {
        return self::all()[$locale]['flag'] ?? 'flags/' . $locale . '.png';
    }

    /**
     * DeepL target-language code, which is not always the locale code.
     */
    public static function deeplCode(string $locale): ?string
    {
        return self::all()[$locale]['deepl'] ?? null;
    }
}
