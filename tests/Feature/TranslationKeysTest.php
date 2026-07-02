<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Tests\TestCase;

class TranslationKeysTest extends TestCase
{
    public function test_front_translation_keys_used_in_blade_views_exist_in_czech_and_english(): void
    {
        $keys = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! Str::endsWith($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = $file->getContents();

            preg_match_all('/(?:__|trans)\(\s*[\'\"](front\.[^\'\"]+)[\'\"]/', $contents, $functionMatches);
            preg_match_all('/@lang\(\s*[\'\"](front\.[^\'\"]+)[\'\"]/', $contents, $directiveMatches);

            $keys = array_merge($keys, $functionMatches[1], $directiveMatches[1]);
        }

        $keys = array_values(array_filter(array_unique($keys), fn (string $key) => ! Str::endsWith($key, '.')));
        sort($keys);

        $missing = [];

        foreach ($keys as $key) {
            foreach (['cs', 'en'] as $locale) {
                if (! Lang::hasForLocale($key, $locale)) {
                    $missing[] = "{$locale}: {$key}";
                }
            }
        }

        $this->assertSame([], $missing, "Missing translation keys:\n".implode("\n", $missing));
    }
}