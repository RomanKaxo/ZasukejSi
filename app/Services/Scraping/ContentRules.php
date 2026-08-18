<?php

namespace App\Services\Scraping;

use App\Models\ScrapeSource;

/**
 * Rules the operator writes about what may enter the queue.
 *
 * The age guard is not negotiable and lives in code. Everything else an
 * operator wants to keep out — agency spam pasted into every description, a
 * city that is not ours, adverts that are plainly copies of one another — is a
 * judgement about this catalogue at this moment, and it changes. Putting it in
 * code means a deploy for every change; putting it in the source's settings
 * means the person who notices the pattern can also stop it.
 *
 * Deliberately narrow: a rule can only ever refuse an item. It cannot rewrite
 * a value, publish anything, or change what a field means — which keeps a
 * mistyped rule to one consequence, and a visible one.
 *
 * The format is one rule per line in the source's `content_rules` setting:
 *
 *     about_me ~ /whatsapp\s*\+\d{6,}/i     odmítni, když popis odpovídá výrazu
 *     city != Brno                          odmítni, když město není Brno
 *     display_name = Anonym                 odmítni, když se jméno rovná
 *     phone empty                           odmítni, když chybí telefon
 *
 * Anything unparseable is skipped rather than guessed at: a rule nobody can
 * read must not quietly start refusing profiles.
 */
class ContentRules
{
    /** @var array<int, string> */
    public const OPERATORS = ['~', '!~', '=', '!=', 'empty', 'not_empty'];

    /**
     * The first rule this item breaks, or null when it breaks none.
     *
     * @param  array<string, mixed>  $values
     */
    public function violation(ScrapeSource $source, array $values): ?string
    {
        foreach ($this->rules($source) as $rule) {
            if ($this->matches($rule, $values)) {
                return $rule['label'];
            }
        }

        return null;
    }

    /**
     * @return array<int, array{field: string, operator: string, argument: string, label: string}>
     */
    public function rules(ScrapeSource $source): array
    {
        $raw = $source->setting('content_rules');

        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $rules = [];

        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $line = trim($line);

            // Prázdné řádky a poznámky, ať se v pravidlech dá vyznat.
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $rule = $this->parse($line);

            if ($rule !== null) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Whether a line is a rule we can act on.
     *
     * Used by the admin to say so before the rule has a chance to refuse
     * anything — a typo that silently does nothing is worse than one that is
     * pointed at.
     */
    public function problems(ScrapeSource $source): array
    {
        $raw = $source->setting('content_rules');

        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $problems = [];

        foreach (preg_split('/\r\n|\r|\n/', $raw) as $number => $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if ($this->parse($line) === null) {
                $problems[] = 'Řádek ' . ($number + 1) . ': nerozumím „' . $line . '".';
            }
        }

        return $problems;
    }

    /** @return array{field: string, operator: string, argument: string, label: string}|null */
    private function parse(string $line): ?array
    {
        // pole operátor argument — argument smí obsahovat mezery i lomítka.
        if (! preg_match('/^([a-z_][a-z0-9_]*)\s+(!~|~|!=|=|empty|not_empty)\s*(.*)$/i', $line, $match)) {
            return null;
        }

        [, $field, $operator, $argument] = $match;

        $argument = trim($argument);

        if (in_array($operator, ['~', '!~'], true)) {
            // Neplatný regulární výraz by shodil celý běh na položce, která s
            // ním nemá nic společného.
            if ($argument === '' || @preg_match($argument, '') === false) {
                return null;
            }
        } elseif (in_array($operator, ['=', '!='], true) && $argument === '') {
            return null;
        }

        return [
            'field' => $field,
            'operator' => $operator,
            'argument' => $argument,
            'label' => $line,
        ];
    }

    /**
     * @param  array{field: string, operator: string, argument: string, label: string}  $rule
     * @param  array<string, mixed>  $values
     */
    private function matches(array $rule, array $values): bool
    {
        $value = $values[$rule['field']] ?? null;

        // Seznamy se posuzují jako text: pravidlo o službách má fungovat,
        // i když jich je osm.
        $text = is_array($value)
            ? implode(' ', array_map(fn ($item) => is_scalar($item) ? (string) $item : '', $value))
            : (is_scalar($value) ? (string) $value : '');

        $empty = trim($text) === '';

        return match ($rule['operator']) {
            'empty' => $empty,
            'not_empty' => ! $empty,
            '~' => ! $empty && (bool) @preg_match($rule['argument'], $text),
            '!~' => $empty || ! @preg_match($rule['argument'], $text),
            '=' => mb_strtolower(trim($text)) === mb_strtolower($rule['argument']),
            '!=' => mb_strtolower(trim($text)) !== mb_strtolower($rule['argument']),
            default => false,
        };
    }
}
