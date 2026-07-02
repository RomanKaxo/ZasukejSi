<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SyncEnglishTranslations extends Command
{
    protected $signature = 'translations:sync-en
                            {--file=* : Specific translation basenames to sync (for example: front.php, auth.php, cs.json)}
                            {--overwrite : Re-translate existing English values instead of filling only missing or copied values}
                            {--dry-run : Preview which strings would be translated without writing files}';

    protected $description = 'Generate English translation files from Czech source files using the configured translator';

    public function handle(): int
    {
        $plans = $this->buildPlans();

        if ($plans === []) {
            $this->info('English translations are already in sync with Czech source files.');

            return self::SUCCESS;
        }

        $totalStrings = array_sum(array_map(static fn (array $plan) => count($plan['jobs']), $plans));

        $this->table(
            ['Target', 'Strings to translate'],
            array_map(static fn (array $plan) => [$plan['label'], count($plan['jobs'])], $plans)
        );

        if ($this->option('dry-run')) {
            $this->warn("Dry run only. {$totalStrings} string(s) would be translated.");

            return self::SUCCESS;
        }

        $apiKey = (string) config('services.deepl.api_key');

        if ($apiKey === '' || $apiKey === 'PASTE_DEEPL_API_KEY_HERE') {
            $this->error('Missing DeepL API key. Set DEEPL_API_KEY in .env or replace PASTE_DEEPL_API_KEY_HERE in config/services.php.');

            return self::FAILURE;
        }

        foreach ($plans as $index => $plan) {
            $this->line("Translating {$plan['label']}...");

            $translations = $this->translateTexts(array_column($plan['jobs'], 'source'));

            foreach ($plan['jobs'] as $jobIndex => $job) {
                if ($plan['type'] === 'php') {
                    $plans[$index]['merged'] = $this->setNestedValue(
                        $plans[$index]['merged'],
                        $job['path'],
                        $translations[$jobIndex]
                    );

                    continue;
                }

                $plans[$index]['merged'][$job['key']] = $translations[$jobIndex];
            }

            $this->writePlan($plans[$index]);
        }

        $this->info("Done. Translated {$totalStrings} string(s) across ".count($plans).' file(s).');

        return self::SUCCESS;
    }

    private function buildPlans(): array
    {
        $plans = [];
        $filters = collect($this->option('file'))
            ->map(static fn (string $value) => Str::lower(trim($value)))
            ->filter()
            ->values()
            ->all();
        $overwrite = (bool) $this->option('overwrite');

        foreach (File::files(lang_path('cs')) as $sourceFile) {
            if ($sourceFile->getExtension() !== 'php') {
                continue;
            }

            if (! $this->matchesFilters($sourceFile->getFilename(), $filters)) {
                continue;
            }

            $targetPath = lang_path('en/'.$sourceFile->getFilename());
            $source = require $sourceFile->getPathname();
            $target = File::exists($targetPath) ? require $targetPath : [];

            if (! is_array($source) || ! is_array($target)) {
                throw new RuntimeException("Translation file {$sourceFile->getFilename()} must return an array.");
            }

            [$merged, $jobs] = $this->preparePhpPlan($source, $target, [], $overwrite);

            if ($jobs === []) {
                continue;
            }

            $plans[] = [
                'type' => 'php',
                'label' => 'lang/en/'.$sourceFile->getFilename(),
                'path' => $targetPath,
                'merged' => $merged,
                'jobs' => $jobs,
            ];
        }

        foreach (['cs.json' => 'en.json'] as $sourceName => $targetName) {
            if (! $this->matchesFilters($sourceName, $filters)) {
                continue;
            }

            $sourcePath = lang_path($sourceName);
            $targetPath = lang_path($targetName);
            $source = File::exists($sourcePath)
                ? json_decode(File::get($sourcePath), true, 512, JSON_THROW_ON_ERROR)
                : [];
            $target = File::exists($targetPath)
                ? json_decode(File::get($targetPath), true, 512, JSON_THROW_ON_ERROR)
                : [];

            [$merged, $jobs] = $this->prepareJsonPlan($source, $target, $overwrite);

            if ($jobs === []) {
                continue;
            }

            $plans[] = [
                'type' => 'json',
                'label' => 'lang/'.$targetName,
                'path' => $targetPath,
                'merged' => $merged,
                'jobs' => $jobs,
            ];
        }

        return $plans;
    }

    private function matchesFilters(string $fileName, array $filters): bool
    {
        if ($filters === []) {
            return true;
        }

        $base = Str::lower($fileName);
        $stem = Str::before($base, '.');

        foreach ($filters as $filter) {
            if ($filter === $base || $filter === $stem) {
                return true;
            }
        }

        return false;
    }

    private function preparePhpPlan(array $source, array $target, array $path, bool $overwrite): array
    {
        $merged = [];
        $jobs = [];

        foreach ($source as $key => $sourceValue) {
            $targetHasKey = array_key_exists($key, $target);
            $targetValue = $targetHasKey ? $target[$key] : null;

            if (is_array($sourceValue)) {
                [$nestedMerged, $nestedJobs] = $this->preparePhpPlan(
                    $sourceValue,
                    is_array($targetValue) ? $targetValue : [],
                    [...$path, $key],
                    $overwrite
                );

                $merged[$key] = $nestedMerged;
                $jobs = [...$jobs, ...$nestedJobs];

                continue;
            }

            if (is_string($sourceValue)) {
                $currentValue = is_string($targetValue) ? $targetValue : null;

                if ($this->shouldTranslate($sourceValue, $currentValue, $overwrite)) {
                    $jobs[] = [
                        'path' => [...$path, $key],
                        'source' => $sourceValue,
                    ];
                    $merged[$key] = $currentValue ?? $sourceValue;
                } else {
                    $merged[$key] = $currentValue;
                }

                continue;
            }

            $merged[$key] = $targetHasKey ? $targetValue : $sourceValue;
        }

        foreach ($target as $key => $targetValue) {
            if (! array_key_exists($key, $source)) {
                $merged[$key] = $targetValue;
            }
        }

        return [$merged, $jobs];
    }

    private function prepareJsonPlan(array $source, array $target, bool $overwrite): array
    {
        $merged = [];
        $jobs = [];

        foreach ($source as $key => $sourceValue) {
            $targetHasKey = array_key_exists($key, $target);
            $targetValue = $targetHasKey ? $target[$key] : null;

            if (! is_string($sourceValue)) {
                $merged[$key] = $targetHasKey ? $targetValue : $sourceValue;
                continue;
            }

            $currentValue = is_string($targetValue) ? $targetValue : null;

            if ($this->shouldTranslate($sourceValue, $currentValue, $overwrite)) {
                $jobs[] = [
                    'key' => $key,
                    'source' => $sourceValue,
                ];
                $merged[$key] = $currentValue ?? $sourceValue;
            } else {
                $merged[$key] = $currentValue;
            }
        }

        foreach ($target as $key => $targetValue) {
            if (! array_key_exists($key, $source)) {
                $merged[$key] = $targetValue;
            }
        }

        return [$merged, $jobs];
    }

    private function shouldTranslate(string $sourceValue, ?string $currentValue, bool $overwrite): bool
    {
        if ($overwrite) {
            return true;
        }

        if ($currentValue === null || trim($currentValue) === '') {
            return true;
        }

        return trim($currentValue) === trim($sourceValue);
    }

    private function translateTexts(array $texts): array
    {
        $results = [];

        foreach (array_chunk($texts, 50) as $chunk) {
            $results = [...$results, ...$this->translateChunk($chunk)];
        }

        return $results;
    }

    private function translateChunk(array $texts): array
    {
        $prepared = [];
        $tokenMaps = [];

        foreach ($texts as $text) {
            [$preparedText, $tokenMap] = $this->protectTokens($text);
            $prepared[] = $preparedText;
            $tokenMaps[] = $tokenMap;
        }

        $payload = [
            'text' => $prepared,
            'source_lang' => 'CS',
            'target_lang' => 'EN',
            'preserve_formatting' => true,
            'formality' => config('services.deepl.formality', 'default'),
        ];

        $glossaryId = (string) config('services.deepl.glossary_id', '');

        if ($glossaryId !== '') {
            $payload['glossary_id'] = $glossaryId;
        }

        $response = Http::timeout(90)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'DeepL-Auth-Key '.config('services.deepl.api_key'),
            ])
            ->post(config('services.deepl.url', 'https://api-free.deepl.com/v2/translate'), $payload)
            ->throw();

        $translations = $response->json('translations');

        if (! is_array($translations) || count($translations) !== count($texts)) {
            throw new RuntimeException('Unexpected translation response payload.');
        }

        return array_map(function (array $translation, int $index) use ($tokenMaps) {
            $translatedText = (string) ($translation['text'] ?? '');

            return $this->restoreTokens($translatedText, $tokenMaps[$index]);
        }, $translations, array_keys($translations));
    }

    private function protectTokens(string $text): array
    {
        $tokenMap = [];
        $counter = 0;

        $protected = preg_replace_callback(
            '/<[^>]+>|:\w+|\{\w+\}|\{\d+\}|%\w+%/u',
            static function (array $matches) use (&$tokenMap, &$counter) {
                $token = '__KEEP_'.$counter.'__';
                $tokenMap[$token] = $matches[0];
                $counter++;

                return $token;
            },
            $text
        );

        return [$protected ?? $text, $tokenMap];
    }

    private function restoreTokens(string $text, array $tokenMap): string
    {
        return strtr($text, $tokenMap);
    }

    private function setNestedValue(array $array, array $path, mixed $value): array
    {
        $ref = &$array;

        foreach ($path as $segment) {
            if (! isset($ref[$segment]) || ! is_array($ref[$segment])) {
                $ref[$segment] ??= [];
            }

            $ref = &$ref[$segment];
        }

        $ref = $value;

        return $array;
    }

    private function writePlan(array $plan): void
    {
        File::ensureDirectoryExists(dirname($plan['path']));

        if ($plan['type'] === 'php') {
            File::put($plan['path'], "<?php\n\nreturn ".$this->exportPhpValue($plan['merged']).";\n");
            return;
        }

        ksort($plan['merged']);
        File::put(
            $plan['path'],
            json_encode($plan['merged'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n"
        );
    }

    private function exportPhpValue(mixed $value, int $indent = 0): string
    {
        if (! is_array($value)) {
            return var_export($value, true);
        }

        if ($value === []) {
            return '[]';
        }

        $padding = str_repeat('    ', $indent);
        $childPadding = str_repeat('    ', $indent + 1);
        $lines = ['['];

        foreach ($value as $key => $item) {
            $renderedKey = is_int($key) ? $key : var_export((string) $key, true);
            $renderedValue = $this->exportPhpValue($item, $indent + 1);
            $lines[] = $childPadding.$renderedKey.' => '.$renderedValue.',';
        }

        $lines[] = $padding.']';

        return implode(PHP_EOL, $lines);
    }
}