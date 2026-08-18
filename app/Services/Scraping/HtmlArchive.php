<?php

namespace App\Services\Scraping;

use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * A ZIP of pages saved from a browser, turned into pages the scraper can read.
 *
 * The hard part is not the ZIP. It is that a saved page does not know where it
 * came from: browsers name the file after the title, so `Kristýna — Brno.html`
 * says nothing about which profile it is. And the address is not decoration —
 * it is what decides whether this is a new profile or one we already have.
 *
 * So the address is looked for in four places, in order of how much they can
 * be trusted:
 *
 *   1. a manifest in the ZIP, because somebody wrote it on purpose,
 *   2. the `saved from url=(…)` comment Chrome and Edge put at the top of
 *      every page saved with „Uložit jako",
 *   3. `<link rel="canonical">`, which is the site's own statement of the
 *      address,
 *   4. `<meta property="og:url">`.
 *
 * A file where none of them is present is reported by name rather than
 * guessed at. Importing a profile under the wrong address would silently
 * overwrite a different one, and that is worse than one line saying „tuhle
 * jsem nepoznal".
 */
class HtmlArchive
{
    /** Ceiling on how many pages one archive may carry. */
    public const MAX_FILES = 500;

    /** A single page bigger than this is not a page. */
    public const MAX_FILE_BYTES = 8 * 1024 * 1024;

    /** Názvy, pod kterými se hledá ruční seznam adres. */
    private const MANIFEST_NAMES = ['manifest.csv', 'urls.csv', 'urls.txt', 'adresy.txt', 'adresy.csv'];

    /**
     * @return array{pages: array<int, array{url: string, html: string, file: string}>, problems: array<int, string>}
     */
    public function read(string $zipPath): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Na serveru chybí rozšíření PHP „zip". Bez něj ZIP otevřít nejde.');
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Soubor se nepodařilo otevřít. Je to opravdu ZIP?');
        }

        try {
            $manifest = $this->manifest($zip);

            $pages = [];
            $problems = [];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);

                if (! $this->isPage($name, $zip->statIndex($i))) {
                    continue;
                }

                if (count($pages) >= self::MAX_FILES) {
                    $problems[] = sprintf('Archiv má víc než %d stránek; zbytek se přeskočil.', self::MAX_FILES);

                    break;
                }

                $html = $zip->getFromIndex($i);

                if (! is_string($html) || trim($html) === '') {
                    $problems[] = $name . ': soubor je prázdný.';

                    continue;
                }

                $url = $manifest[$this->basename($name)] ?? $this->urlFrom($html);

                if ($url === null) {
                    $problems[] = $name . ': nepodařilo se zjistit adresu profilu. '
                        . 'Uložte stránku z Chrome nebo Edge („Uložit jako"), nebo přidejte do archivu manifest.csv.';

                    continue;
                }

                $pages[] = ['url' => $url, 'html' => $html, 'file' => $name];
            }

            return ['pages' => $pages, 'problems' => $problems];
        } finally {
            $zip->close();
        }
    }

    /**
     * The address a saved page carries inside it.
     *
     * Public because the workbench and the single-page paste use the same
     * trick: if the operator saved the page from a browser, they should not
     * have to type the address again.
     */
    public function urlFrom(string $html): ?string
    {
        // Chrome i Edge tohle vkládají na úplný začátek uložené stránky.
        if (preg_match('/<!--\s*saved from url=\(\d+\)(\S+?)\s*-->/i', $html, $m)) {
            return $this->clean($m[1]);
        }

        if (preg_match('#<link[^>]+rel\s*=\s*["\']canonical["\'][^>]*href\s*=\s*["\']([^"\']+)#i', $html, $m)) {
            return $this->clean($m[1]);
        }

        // Pořadí atributů bývá i obrácené.
        if (preg_match('#<link[^>]+href\s*=\s*["\']([^"\']+)["\'][^>]*rel\s*=\s*["\']canonical["\']#i', $html, $m)) {
            return $this->clean($m[1]);
        }

        if (preg_match('#<meta[^>]+property\s*=\s*["\']og:url["\'][^>]*content\s*=\s*["\']([^"\']+)#i', $html, $m)) {
            return $this->clean($m[1]);
        }

        return null;
    }

    /**
     * Filename => address, from a list somebody wrote by hand.
     *
     * Two columns separated by a comma, semicolon or tab; one pair per line.
     * Deliberately forgiving about which, because the file is going to be
     * produced by a spreadsheet or by hand and neither agrees on a separator.
     *
     * @return array<string, string>
     */
    private function manifest(ZipArchive $zip): array
    {
        foreach (self::MANIFEST_NAMES as $candidate) {
            $index = $zip->locateName($candidate, ZipArchive::FL_NOCASE | ZipArchive::FL_NODIR);

            if ($index === false) {
                continue;
            }

            $raw = $zip->getFromIndex($index);

            if (! is_string($raw)) {
                continue;
            }

            $map = [];

            foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                $parts = preg_split('/[;,\t]/', $line, 2);

                if (count($parts) !== 2) {
                    continue;
                }

                $file = trim($parts[0], " \t\"'");
                $url = $this->clean(trim($parts[1], " \t\"'"));

                if ($file !== '' && $url !== null) {
                    $map[$this->basename($file)] = $url;
                }
            }

            return $map;
        }

        return [];
    }

    /** @param array<string, mixed>|false $stat */
    private function isPage(string $name, array|false $stat): bool
    {
        // Adresáře, systémové smetí z macOS a všechno, co není stránka.
        if (str_ends_with($name, '/') || str_starts_with($name, '__MACOSX/') || str_contains($name, '/._')) {
            return false;
        }

        if (! preg_match('/\.(html?|xhtml)$/i', $name)) {
            return false;
        }

        return ! is_array($stat) || ($stat['size'] ?? 0) <= self::MAX_FILE_BYTES;
    }

    private function basename(string $path): string
    {
        return mb_strtolower(basename(str_replace('\\', '/', $path)));
    }

    /** Only an http(s) address is an address. */
    private function clean(string $url): ?string
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        try {
            $scheme = parse_url($url, PHP_URL_SCHEME);
        } catch (Throwable) {
            return null;
        }

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }
}
