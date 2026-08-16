<?php

namespace Tests\Feature;

use App\Support\Locales;
use Tests\TestCase;

/**
 * The flags are pre-rounded images rather than a square cropped by CSS, so a
 * new locale added with a plain rectangular flag shows up square next to the
 * round ones — which is exactly what happened to Russian.
 */
class LocaleFlagAssetTest extends TestCase
{
    /**
     * Data providers run before the application boots, so `config()` is not
     * available yet — the file is read directly instead.
     */
    public static function locales(): array
    {
        $config = require dirname(__DIR__, 2) . '/config/locales.php';

        return array_map(
            fn (string $locale) => [$locale],
            array_combine(array_keys($config['supported']), array_keys($config['supported']))
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('locales')]
    public function test_every_locale_flag_is_a_round_asset(string $locale): void
    {
        if (! function_exists('imagecreatefrompng')) {
            $this->markTestSkipped('ext-gd není k dispozici.');
        }

        $path = public_path(Locales::flag($locale));

        $this->assertFileExists($path, "Vlajka pro '{$locale}' chybí.");

        [$width, $height] = getimagesize($path);

        // Square, so `object-fit` has nothing to crop unevenly.
        $this->assertLessThanOrEqual(
            1,
            abs($width - $height),
            "Vlajka '{$locale}' není čtvercová ({$width}x{$height})."
        );

        $image = imagecreatefrompng($path);
        $corner = imagecolorat($image, 0, 0);
        $centre = imagecolorat($image, intdiv($width, 2), intdiv($height, 2));

        // A rounded flag has a transparent corner and an opaque centre.
        $this->assertSame(127, ($corner >> 24) & 0x7F, "Vlajka '{$locale}' má neprůhledný roh — není zakulacená.");
        $this->assertSame(0, ($centre >> 24) & 0x7F, "Vlajka '{$locale}' je průhledná i uprostřed.");
    }
}
