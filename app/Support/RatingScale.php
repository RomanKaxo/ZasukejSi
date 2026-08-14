<?php

namespace App\Support;

use App\Models\Setting;

/**
 * The single definition of the rating scale.
 *
 * Ratings are a percentage from 1 to 100. The member UI offers three presets
 * (Figma fixes their number and placement, but not their values), and stars
 * are only a display projection of the percentage — never the stored truth.
 */
class RatingScale
{
    public const KEY_HIGH = 'ratings.option_high';
    public const KEY_MID = 'ratings.option_mid';
    public const KEY_LOW = 'ratings.option_low';

    public const DEFAULT_HIGH = 100;
    public const DEFAULT_MID = 70;
    public const DEFAULT_LOW = 30;

    /** Bar colour thresholds, shared by the member history and the admin table. */
    public const THRESHOLD_GOOD = 80;
    public const THRESHOLD_FAIR = 40;

    /**
     * The three preset percentages, highest first, as offered in the member UI.
     *
     * @return array{high: int, mid: int, low: int}
     */
    public static function options(): array
    {
        return [
            'high' => self::clamp(Setting::getInt(self::KEY_HIGH, self::DEFAULT_HIGH)),
            'mid' => self::clamp(Setting::getInt(self::KEY_MID, self::DEFAULT_MID)),
            'low' => self::clamp(Setting::getInt(self::KEY_LOW, self::DEFAULT_LOW)),
        ];
    }

    /** Whether a percentage is one the UI is currently allowed to submit. */
    public static function isOffered(int $percentage): bool
    {
        return in_array($percentage, array_values(self::options()), true);
    }

    /** Constrain any percentage to the valid 1-100 range. */
    public static function clamp(int $percentage): int
    {
        return max(1, min(100, $percentage));
    }

    /**
     * Percentage projected onto the 1-5 star scale for display.
     *
     * Kept as a float so an average of 70% reads as 3.5/5 rather than being
     * rounded up to the 4 stars that made 70% look like 80%.
     */
    public static function toStars(float $percentage): float
    {
        return round($percentage / 20, 1);
    }

    /**
     * Percentage projected onto whole stars, for the integer `rating` mirror
     * column and for drawing star icons.
     */
    public static function toWholeStars(float $percentage): int
    {
        return max(1, min(5, (int) round($percentage / 20)));
    }

    /** Bar colour for a percentage, matching the member ratings history. */
    public static function color(float $percentage): string
    {
        return match (true) {
            $percentage >= self::THRESHOLD_GOOD => '#00B80F',
            $percentage >= self::THRESHOLD_FAIR => '#FFB700',
            default => '#F47216',
        };
    }
}
