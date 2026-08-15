<?php

namespace App\Services\Scraping\Contracts;

use App\Models\ScrapeSource;

/**
 * What differs between sites beyond selectors: how listings are paged, how
 * detail links are found, and what counts as the site's own id for a profile.
 *
 * A site that fits the usual "listing page with links, ?page=N" shape needs no
 * class at all — GenericAdapter covers it from settings.
 */
interface SourceAdapter
{
    /** The listing URL for a given 1-based page. */
    public function listingUrl(ScrapeSource $source, int $page): string;

    /**
     * Absolute detail URLs found on a listing page.
     *
     * @return array<int, string>
     */
    public function detailUrls(ScrapeSource $source, string $listingHtml): array;

    /** The site's own identifier for a profile, used to deduplicate. */
    public function externalId(ScrapeSource $source, string $detailUrl): ?string;

    /**
     * Image URLs from a detail page, already absolute.
     *
     * @return array<int, string>
     */
    public function imageUrls(ScrapeSource $source, string $detailHtml): array;
}
