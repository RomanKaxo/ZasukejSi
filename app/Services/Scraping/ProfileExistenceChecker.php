<?php

namespace App\Services\Scraping;

use App\Models\Notification;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use Closure;
use RuntimeException;
use Throwable;

/**
 * Checks that imported profiles still exist where they came from.
 *
 * A woman who stops advertising disappears from the source, and nothing here
 * noticed: her profile stayed on the site, public and looking current, for
 * good. Of everything a scraped catalogue can get wrong, that is the one that
 * costs the visitor something real — a listing that leads nowhere.
 *
 * Two rules govern the whole class.
 *
 * The first is that one 404 proves nothing. Sites move pages, break for an
 * afternoon, and return odd statuses to a bot they do not recognise, so a
 * profile is only called gone after the same answer several times, on
 * different days.
 *
 * The second is that **nothing is ever deleted or hidden here**. Vanishing
 * from a source has several innocent explanations and one bad one, and telling
 * them apart is a judgement about a real person's listing. The checker's whole
 * output is a flag and a notification; what happens next is the operator's
 * decision, made in the admin.
 */
class ProfileExistenceChecker
{
    /** How many consecutive misses before we call it gone. */
    private const DEFAULT_CONFIRMATIONS = 2;

    /** How long to leave a profile alone between checks. */
    private const DEFAULT_INTERVAL_HOURS = 24;

    /** Ceiling per run, so a check never turns into a harvest. */
    private const DEFAULT_BATCH = 100;

    public function __construct(private readonly HttpFetcher $fetcher)
    {
    }

    /**
     * @param  Closure(string): void|null  $notify
     * @return array{checked: int, missing: int, recovered: int}
     */
    public function check(ScrapeSource $source, ?int $limit = null, ?Closure $notify = null): array
    {
        $say = $notify ?? static fn () => null;

        $items = ScrapeItem::query()
            ->where('scrape_source_id', $source->id)
            ->whereNotNull('imported_profile_id')
            // Anything already decided stays decided: re-asking about a
            // profile the operator chose to keep is nagging, not diligence.
            ->whereNull('missing_resolution')
            ->where(function ($query) use ($source) {
                $query
                    ->whereNull('missing_checked_at')
                    ->orWhere('missing_checked_at', '<=', now()->subHours($this->interval($source)));
            })
            ->orderBy('missing_checked_at')
            ->limit($limit ?: (int) $source->setting('existence_batch', self::DEFAULT_BATCH))
            ->get();

        $checked = 0;
        $missing = 0;
        $recovered = 0;

        foreach ($items as $item) {
            $present = $this->stillThere($source, $item->source_url);

            // Null means we could not tell — a timeout, a 500, a refusal. That
            // is not evidence of absence and must not be counted as one.
            if ($present === null) {
                $item->forceFill(['missing_checked_at' => now()])->save();

                continue;
            }

            $checked++;

            if ($present) {
                if ($item->missing_since !== null) {
                    $recovered++;
                    $say('Zase je tam: ' . $item->source_url);
                }

                $item->forceFill([
                    'missing_since' => null,
                    'missing_checks' => 0,
                    'missing_checked_at' => now(),
                ])->save();

                continue;
            }

            $checks = (int) $item->missing_checks + 1;

            $item->forceFill([
                'missing_checks' => $checks,
                'missing_checked_at' => now(),
                // The date is the first miss, not the confirming one — it is
                // what answers „how long has this been dead?".
                'missing_since' => $checks >= $this->confirmations($source)
                    ? ($item->missing_since ?? now())
                    : $item->missing_since,
            ])->save();

            if ($item->fresh()->isAwaitingRemovalDecision()) {
                $missing++;
                $say('Zmizel ze zdroje: ' . $item->source_url);
            }
        }

        if ($missing > 0) {
            $this->announce($source, $missing);
        }

        return ['checked' => $checked, 'missing' => $missing, 'recovered' => $recovered];
    }

    /**
     * Whether the page is still there.
     *
     * true = present, false = gone, null = could not tell.
     */
    private function stillThere(ScrapeSource $source, string $url): ?bool
    {
        try {
            $this->fetcher->get($source, $url);

            return true;
        } catch (RuntimeException $e) {
            $message = $e->getMessage();

            // Only the statuses that actually mean „this is not here": 404 and
            // 410. A 403 means the site is refusing us, which says nothing
            // about whether the profile exists.
            if (str_contains($message, 'HTTP 404') || str_contains($message, 'HTTP 410')) {
                return false;
            }

            return null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Tell the operator, once per source per run.
     *
     * A notification rather than a silent flag, because a queue nobody is told
     * about is a queue nobody empties.
     */
    private function announce(ScrapeSource $source, int $count): void
    {
        Notification::forAdmins(
            'Profily zmizely ze zdroje',
            sprintf(
                'Na webu %s už není %d profilů, které u nás pořád jsou. Rozhodněte v administraci, co s nimi — samo se nic nesmaže.',
                $source->name,
                $count,
            ),
        );
    }

    private function confirmations(ScrapeSource $source): int
    {
        return max(1, (int) $source->setting('existence_confirmations', self::DEFAULT_CONFIRMATIONS));
    }

    private function interval(ScrapeSource $source): int
    {
        return max(1, (int) $source->setting('existence_interval_hours', self::DEFAULT_INTERVAL_HOURS));
    }
}
