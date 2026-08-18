<?php

namespace App\Services\Scraping;

use App\Models\ScrapeSource;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Works out *why* a site is refusing us, and what would fix it.
 *
 * „HTTP 403 — web nás odmítl" is true and useless. It does not say whether the
 * site is refusing this server's address, this bot's name, or every request
 * that does not come from a real browser — and those three have three
 * different cures. Worse, the answer differs by where you ask from: the same
 * address answers 200 from a developer's laptop and 403 from the server, so
 * guessing at it locally is guessing.
 *
 * This runs a short ladder of attempts from wherever it is called and reports
 * which rung got through. One click, no guessing, and if something worked it
 * hands back the exact settings to save.
 *
 * What it does not do is fight anything. Where the refusal comes from a
 * protection service that fingerprints the connection rather than the headers,
 * it says so plainly instead of pretending another User-Agent will help.
 */
class ConnectionDoctor
{
    /** How long any single attempt may take. */
    private const TIMEOUT = 20;

    /**
     * Headers a current Chrome actually sends.
     *
     * Not a disguise for its own sake: many blanket rules refuse anything
     * whose header set does not look like a browser, including honestly
     * declared crawlers that the site's own robots.txt permits. Whether to use
     * this is a decision for the operator, which is why it is offered as a
     * result rather than applied silently.
     *
     * @return array<string, string>
     */
    public static function browserHeaders(string $host): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
                . '(KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'cs-CZ,cs;q=0.9,en;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
            'Sec-Ch-Ua' => '"Chromium";v="126", "Not:A-Brand";v="24"',
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => '"Windows"',
            'Connection' => 'keep-alive',
            'Host' => $host,
        ];
    }

    /**
     * Run the ladder and report what happened on each rung.
     *
     * @return array{
     *     url: string,
     *     attempts: array<int, array<string, mixed>>,
     *     protection: ?string,
     *     verdict: string,
     *     suggestion: array<string, string>,
     * }
     */
    public function diagnose(ScrapeSource $source, ?string $url = null): array
    {
        $url = $url ?: $source->base_url;
        $host = parse_url($url, PHP_URL_HOST) ?: '';
        $root = rtrim($source->base_url, '/');

        $attempts = [];

        // 1) Přesně to, co dělá scraper teď.
        $attempts[] = $this->attempt(
            'Současné nastavení zdroje',
            $url,
            app(HttpFetcher::class)->headers($source),
            $source,
        );

        // 2) Hlavičky skutečného prohlížeče.
        $attempts[] = $this->attempt(
            'Hlavičky jako prohlížeč',
            $url,
            self::browserHeaders($host),
            $source,
        );

        // 3) Prohlížeč, který na stránku přišel z toho webu.
        $attempts[] = $this->attempt(
            'Prohlížeč + Referer z téhož webu',
            $url,
            self::browserHeaders($host) + ['Referer' => $root . '/'],
            $source,
        );

        // 4) Pustí nás web aspoň k robots.txt? Odpovídá na otázku, jestli je
        //    zablokovaná celá adresa serveru, nebo jen tahle cesta.
        $attempts[] = $this->attempt(
            'robots.txt se současným nastavením',
            $root . '/robots.txt',
            app(HttpFetcher::class)->headers($source),
            $source,
            probe: true,
        );

        // 5) Domovská stránka. Když projde a detail ne, je to o cestě.
        if (rtrim($url, '/') !== $root) {
            $attempts[] = $this->attempt(
                'Domovská stránka jako prohlížeč',
                $root . '/',
                self::browserHeaders($host),
                $source,
                probe: true,
            );
        }

        $protection = $this->protectionFrom($attempts);

        return [
            'url' => $url,
            'attempts' => $attempts,
            'protection' => $protection,
            'verdict' => $this->verdict($attempts, $protection),
            'suggestion' => $this->suggestion($attempts, $host, $root),
        ];
    }

    /**
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    private function attempt(string $label, string $url, array $headers, ScrapeSource $source, bool $probe = false): array
    {
        $result = [
            'label' => $label,
            'url' => $url,
            // Sonda odpovídá na vedlejší otázku (pustí nás web vůbec?), ne na
            // tu hlavní. Kdyby se počítala mezi úspěchy, verdikt by hlásil, že
            // to prošlo — u adresy, o kterou vůbec nešlo.
            'probe' => $probe,
            'status' => null,
            'ok' => false,
            'server' => null,
            'protection' => null,
            'body' => null,
            'error' => null,
        ];

        try {
            $request = Http::withHeaders($headers)
                ->timeout(self::TIMEOUT)
                ->withoutRedirecting()
                ->retry(1, 0, throw: false);

            $proxy = $source->setting('proxy');

            if (is_string($proxy) && trim($proxy) !== '') {
                $request = $request->withOptions(['proxy' => trim($proxy)]);
            }

            $response = $request->get($url);
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();

            return $result;
        }

        $body = (string) $response->body();

        $result['status'] = $response->status();
        $result['ok'] = $response->successful();
        $result['server'] = $response->header('Server') ?: null;
        $result['protection'] = $this->protectionSignature($response, $body);
        // Kousek těla: stránka blokace se pozná na první pohled a bez ní se
        // hádá, kdo vlastně odmítl.
        $result['body'] = $this->snippet($body);

        return $result;
    }

    /**
     * Which protection service answered, if any.
     *
     * The point is that these need different cures. A rule that refuses
     * unknown User-Agents is fixed by changing headers; one that fingerprints
     * the TLS handshake is not fixed by anything this code can send, and
     * saying so saves an afternoon.
     */
    private function protectionSignature(Response $response, string $body): ?string
    {
        $haystack = strtolower($body);

        // Pořadí od nejkonkrétnějšího: kontrola prohlížeče se řeší úplně jinak
        // než prostá blokace, a hlavička cf-ray je na obojím.
        return match (true) {
            str_contains($haystack, 'just a moment') || str_contains($haystack, 'enable javascript and cookies')
                => 'Cloudflare — kontrola prohlížeče (vyžaduje JavaScript)',
            str_contains($haystack, 'attention required') || str_contains($haystack, 'cf-error')
                => 'Cloudflare (stránka blokace)',
            $response->header('cf-ray') !== '' && $response->header('cf-ray') !== null
                => 'Cloudflare (hlavička cf-ray)',
            $response->header('x-datadome') !== '' && $response->header('x-datadome') !== null
                => 'DataDome',
            str_contains($haystack, 'incapsula') || str_contains($haystack, '_incap_')
                => 'Imperva / Incapsula',
            str_contains($haystack, 'akamai') && str_contains($haystack, 'reference #')
                => 'Akamai',
            str_contains($haystack, 'access denied') || str_contains($haystack, 'přístup odepřen')
                => 'Obecná stránka „přístup odepřen"',
            default => null,
        };
    }

    /** @param array<int, array<string, mixed>> $attempts */
    private function protectionFrom(array $attempts): ?string
    {
        foreach ($attempts as $attempt) {
            if ($attempt['protection'] !== null) {
                return $attempt['protection'];
            }
        }

        return null;
    }

    /** @param array<int, array<string, mixed>> $attempts */
    private function verdict(array $attempts, ?string $protection): string
    {
        $worked = array_values(array_filter($attempts, fn (array $a) => $a['ok'] && ! $a['probe']));

        if ($worked !== [] && $worked[0]['label'] === 'Současné nastavení zdroje') {
            return 'Web odpovídá i se současným nastavením. Blokace tady není.';
        }

        if ($worked !== []) {
            return 'Prošlo: „' . $worked[0]['label'] . '". Níže je nastavení, které to zařídí natrvalo.';
        }

        if ($protection !== null && str_contains($protection, 'JavaScript')) {
            return 'Web má před sebou kontrolu prohlížeče, která vyžaduje JavaScript. Žádná kombinace hlaviček '
                . 'to neobejde — pomůže jedině stahování přes externí renderer (nastavení `render_endpoint`) '
                . 'nebo proxy s adresou, které web důvěřuje.';
        }

        if ($protection !== null) {
            return 'Odmítá nás ochranná služba: ' . $protection . '. Změna hlaviček nepomohla, takže rozhoduje '
                . 'nejspíš adresa serveru. Řešením je proxy u zdroje, nebo domluva s provozovatelem webu.';
        }

        $robots = array_values(array_filter($attempts, fn (array $a) => str_contains($a['label'], 'robots.txt')));

        if ($robots !== [] && $robots[0]['ok']) {
            return 'robots.txt se stáhnout dá, ale zkoumaná stránka ne. Blokace je na té cestě, ne na adrese '
                . 'serveru — zkuste jiný vstupní bod nebo se podívejte, jestli stránka existuje.';
        }

        return 'Neprošlo nic, ani robots.txt. Vypadá to na blokaci adresy tohohle serveru. '
            . 'Pomůže proxy u zdroje, nebo stahovat odjinud.';
    }

    /**
     * The settings that would make the working attempt permanent.
     *
     * @param  array<int, array<string, mixed>>  $attempts
     * @return array<string, string>
     */
    private function suggestion(array $attempts, string $host, string $root): array
    {
        // Současné nastavení funguje — navrhovat změnu by bylo řešení bez
        // problému.
        if (($attempts[0]['ok'] ?? false) === true) {
            return [];
        }

        foreach ($attempts as $attempt) {
            if (! $attempt['ok'] || $attempt['label'] === 'Současné nastavení zdroje') {
                continue;
            }

            if (str_contains($attempt['label'], 'Referer')) {
                $headers = self::browserHeaders($host) + ['Referer' => $root . '/'];
            } elseif (str_contains($attempt['label'], 'prohlížeč')) {
                $headers = self::browserHeaders($host);
            } else {
                continue;
            }

            $userAgent = $headers['User-Agent'];
            unset($headers['User-Agent'], $headers['Host'], $headers['Accept-Encoding'], $headers['Connection']);

            return [
                'user_agent' => $userAgent,
                'headers' => json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ];
        }

        return [];
    }

    /** Kousek těla odpovědi, bez značek a zkrácený. */
    private function snippet(string $body): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags(
            preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $body) ?? ''
        )) ?? '');

        if ($text === '') {
            return null;
        }

        return mb_strlen($text) > 300 ? mb_substr($text, 0, 300) . '…' : $text;
    }
}
