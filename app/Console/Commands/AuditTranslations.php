<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class AuditTranslations extends Command
{
    protected $signature = 'translations:audit
                            {--strict-hardcoded : Fail when suspicious hardcoded UI text is found in Blade views}';

    protected $description = 'Audit Blade and app translation usage across Czech and English locales';

    public function handle(): int
    {
        $viewFiles = $this->collectFiles(resource_path('views'), ['vendor']);
        $appFiles = $this->collectFiles(app_path(), ['Filament']);

        [$keyedCalls, $jsonCalls] = $this->collectTranslationCalls($viewFiles->merge($appFiles));

        $missingKeyed = $this->findMissingKeyedTranslations($keyedCalls);
        $missingJson = $this->findMissingJsonTranslations($jsonCalls);
        $hardcodedTexts = $this->findSuspiciousHardcodedText($viewFiles);

        $hasFailures = false;

        if ($missingKeyed !== []) {
            $hasFailures = true;
            $this->error('Missing keyed translations:');
            foreach ($missingKeyed as $line) {
                $this->line("  - {$line}");
            }
            $this->newLine();
        }

        if ($missingJson !== []) {
            $hasFailures = true;
            $this->error('Missing JSON translations:');
            foreach ($missingJson as $line) {
                $this->line("  - {$line}");
            }
            $this->newLine();
        }

        if ($hardcodedTexts !== []) {
            $this->warn('Suspicious hardcoded UI text found in Blade views:');
            foreach (array_slice($hardcodedTexts, 0, 50) as $entry) {
                $this->line("  - {$entry}");
            }

            if (count($hardcodedTexts) > 50) {
                $remaining = count($hardcodedTexts) - 50;
                $this->line("  ... and {$remaining} more");
            }

            $this->newLine();

            if ($this->option('strict-hardcoded')) {
                $hasFailures = true;
            }
        }

        if ($hasFailures) {
            $this->error('Translation audit failed.');
            return self::FAILURE;
        }

        $this->info('Translation audit passed.');
        return self::SUCCESS;
    }

    private function collectFiles(string $root, array $excludedSegments = []): Collection
    {
        return collect(File::allFiles($root))
            ->filter(function ($file) use ($excludedSegments) {
                $path = str_replace('\\', '/', $file->getPathname());

                foreach ($excludedSegments as $segment) {
                    if (str_contains($path, '/'.$segment.'/')) {
                        return false;
                    }
                }

                return true;
            });
    }

    private function collectTranslationCalls(Collection $files): array
    {
        $keyedCalls = [];
        $jsonCalls = [];

        foreach ($files as $file) {
            $contents = $file->getContents();
            $path = $this->normalizePath($file->getPathname());

            foreach ($this->extractTranslationLiterals($contents) as $entry) {
                [$key, $line] = $entry;

                if (str_contains($key, '::') || Str::endsWith($key, '.')) {
                    continue;
                }

                if (str_contains($key, '.')) {
                    $keyedCalls[$key] ??= [];
                    $keyedCalls[$key][] = "{$path}:{$line}";
                    continue;
                }

                $jsonCalls[$key] ??= [];
                $jsonCalls[$key][] = "{$path}:{$line}";
            }
        }

        ksort($keyedCalls);
        ksort($jsonCalls);

        return [$keyedCalls, $jsonCalls];
    }

    private function extractTranslationLiterals(string $contents): array
    {
        $results = [];

        foreach (preg_split('/\R/', $contents) as $lineNumber => $lineContent) {
            $cursor = 0;

            while (preg_match('/(?:__|trans|@lang)\(\s*([\'\"])/', $lineContent, $match, PREG_OFFSET_CAPTURE, $cursor)) {
                $quote = $match[1][0];
                $quotePosition = $match[1][1];
                $key = '';
                $escaped = false;
                $endPosition = null;

                for ($index = $quotePosition + 1, $length = strlen($lineContent); $index < $length; $index++) {
                    $char = $lineContent[$index];

                    if ($escaped) {
                        $key .= $char;
                        $escaped = false;
                        continue;
                    }

                    if ($char === '\\') {
                        $escaped = true;
                        continue;
                    }

                    if ($char === $quote) {
                        $endPosition = $index;
                        break;
                    }

                    $key .= $char;
                }

                if ($endPosition === null) {
                    break;
                }

                $results[] = [stripcslashes($key), $lineNumber + 1];
                $cursor = $endPosition + 1;
            }
        }

        return $results;
    }

    private function findMissingKeyedTranslations(array $keyedCalls): array
    {
        $missing = [];

        foreach ($keyedCalls as $key => $locations) {
            foreach (['cs', 'en'] as $locale) {
                if (! Lang::hasForLocale($key, $locale)) {
                    $missing[] = "{$locale}: {$key} (used in {$locations[0]})";
                }
            }
        }

        return $missing;
    }

    private function findMissingJsonTranslations(array $jsonCalls): array
    {
        $missing = [];

        foreach ($jsonCalls as $key => $locations) {
            foreach (['cs', 'en'] as $locale) {
                $jsonPath = lang_path("{$locale}.json");
                $translations = File::exists($jsonPath)
                    ? json_decode(File::get($jsonPath), true, 512, JSON_THROW_ON_ERROR)
                    : [];

                if (! array_key_exists($key, $translations)) {
                    $missing[] = "{$locale}: {$key} (used in {$locations[0]})";
                }
            }
        }

        return $missing;
    }

    private function findSuspiciousHardcodedText(Collection $viewFiles): array
    {
        $issues = [];

        foreach ($viewFiles as $file) {
            $path = $this->normalizePath($file->getPathname());
            $contents = $file->getContents();

            $sanitized = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $contents);
            $sanitized = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $sanitized);
            $sanitized = preg_replace('/\{\{.*?\}\}|\{!!.*?!!\}/s', '', $sanitized);
            $sanitized = preg_replace('/@php.*?@endphp/s', '', $sanitized);
            $sanitized = preg_replace('/{{--.*?--}}/s', '', $sanitized);
            $sanitized = preg_replace('/@\w+(?:\s*\([^\n]*?\))?/u', '', $sanitized);

            preg_match_all('/>([^<>]+)</u', $sanitized, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[1] ?? [] as [$rawText, $offset]) {
                $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode($rawText, ENT_QUOTES | ENT_HTML5)));

                if ($text === '' || mb_strlen($text) < 2) {
                    continue;
                }

                if (! preg_match('/\p{L}/u', $text)) {
                    continue;
                }

                if (preg_match('/^(if|else|endif|foreach|endforeach|for|endfor)$/i', $text)) {
                    continue;
                }

                if (preg_match('/[@$(){};]/', $text)) {
                    continue;
                }

                $line = substr_count(substr($contents, 0, $offset), "\n") + 1;
                $issues[] = "{$path}:{$line} -> {$text}";
            }

            preg_match_all('/(?:title|placeholder|aria-label|alt)\s*=\s*"([^"{}@]+)"/u', $contents, $attributeMatches, PREG_OFFSET_CAPTURE);
            foreach ($attributeMatches[1] ?? [] as [$rawText, $offset]) {
                $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode($rawText, ENT_QUOTES | ENT_HTML5)));

                if ($text === '' || ! preg_match('/\p{L}/u', $text)) {
                    continue;
                }

                $line = substr_count(substr($contents, 0, $offset), "\n") + 1;
                $issues[] = "{$path}:{$line} -> {$text}";
            }
        }

        return array_values(array_unique($issues));
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', Str::after($path, base_path().DIRECTORY_SEPARATOR));
    }
}