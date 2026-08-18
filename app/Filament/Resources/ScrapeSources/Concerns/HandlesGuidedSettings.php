<?php

namespace App\Filament\Resources\ScrapeSources\Concerns;

/**
 * Bridges the guided form fields and the `settings` JSON column.
 *
 * Everything a source needs used to live in one free-text key/value editor.
 * That is fine for the person who wrote the scraper and useless for anybody
 * else: nothing said which keys exist, what values they take, or that
 * `discovery` had to be spelled exactly so.
 *
 * The keys listed here get real form controls — a select, a toggle, a number
 * — and are taken out of the raw editor while the form is open, so a setting
 * is never editable in two places at once. Everything else stays in the raw
 * editor, which is what keeps a new key from needing a deploy.
 */
trait HandlesGuidedSettings
{
    /** Settings with a form control of their own. */
    protected const GUIDED_KEYS = [
        'discovery',
        'sitemap_url',
        'sitemap_changed_only',
        'pagination_mode',
        'next_link_selector',
        'conditional_requests',
        'proxy',
        'auto_pause',
        'failure_threshold',
        'max_attempts',
        'run_window_from',
        'run_window_to',
        'run_days',
    ];

    /** @param array<string, mixed> $data */
    protected function unpackGuidedSettings(array $data): array
    {
        $settings = is_array($data['settings'] ?? null) ? $data['settings'] : [];

        foreach (self::GUIDED_KEYS as $key) {
            $data[$key] = $settings[$key] ?? null;
            unset($settings[$key]);
        }

        $data['settings'] = $settings;

        return $data;
    }

    /** @param array<string, mixed> $data */
    protected function packGuidedSettings(array $data): array
    {
        $settings = is_array($data['settings'] ?? null) ? $data['settings'] : [];

        foreach (self::GUIDED_KEYS as $key) {
            $value = $data[$key] ?? null;
            unset($data[$key]);

            // An empty field means „use the shipped default", which is what
            // leaving the key out says. Writing null would be the same thing
            // said louder, and would clutter the raw editor with dead rows.
            if ($value === null || $value === '') {
                unset($settings[$key]);

                continue;
            }

            $settings[$key] = $value;
        }

        $data['settings'] = $settings;

        return $data;
    }
}
