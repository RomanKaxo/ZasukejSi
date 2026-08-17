<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;

/**
 * Who has the sliders on the profile detail held back from them.
 *
 * The design and the brief disagreed here, and the audit could not decide it
 * from the outside. The design blurs every card in „Nejlépe hodnocené dívky"
 * and puts a VIP badge on all of them; the brief said only VIP profiles are
 * hidden. Both readings are defensible — the caption „Premium účet vám odemkne
 * hodnocení" says the gate is the viewer's membership, the badges say it is the
 * advertiser's tier.
 *
 * So it is a setting rather than a decision baked into a Blade template.
 */
class TopRatedLock
{
    public const KEY = 'top_rated.lock_mode';

    /** Blurred for anyone without an active Premium — what the design draws. */
    public const MODE_PREMIUM = 'premium';

    /** Blurred only on VIP profiles, whoever is looking — the earlier brief. */
    public const MODE_VIP = 'vip';

    public const DEFAULT = self::MODE_PREMIUM;

    /** @return array<string, string> value => label for the admin select */
    public static function options(): array
    {
        return [
            self::MODE_PREMIUM => 'Vidí jen uživatelé s aktivním Premium',
            self::MODE_VIP => 'Skryté jsou jen VIP profily (dosavadní chování)',
        ];
    }

    public static function mode(): string
    {
        $mode = (string) Setting::get(self::KEY, self::DEFAULT);

        return array_key_exists($mode, self::options()) ? $mode : self::DEFAULT;
    }

    /**
     * Whether this card on the detail page's sliders is held back.
     *
     * @param  bool  $isVip  Whether the advertised profile is VIP.
     */
    public static function shouldBlur(bool $isVip): bool
    {
        return self::mode() === self::MODE_PREMIUM
            // Providers and admins are never locked out; the Gate already says
            // so, and this keeps the two rules in one place.
            ? ! Gate::allows('view-ratings')
            : $isVip;
    }
}
