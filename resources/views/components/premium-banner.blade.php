{{--
    The green bar the design puts above every member page.

    This markup used to be pasted into five member views, each carrying the
    literal date 12. 12. 2025, so every member was told the same thing
    regardless of reality — and nothing backed it, because subscriptions were
    keyed on `profile_id` and a member has no profile.

    Two states, both from the design:
      · `muž - záložky-1`  — „Vaše Premium členství platí do 12. 12. 2025"
      · `phone 360 px-1`   — „Máte aktivní členství Premium už jen 5 dní –
                              prodloužení členství zde"

    The second is what the design shows when the end is near, so that is when
    it is used. The link to extend is offered in both, because a member who
    wants to renew early should not have to go looking for it.
--}}
@php
    $bannerUser = auth()->user();
    $membershipEndsAt = $bannerUser?->membershipEndsAt();

    // Below this the bar counts down instead of naming a date.
    $expiringSoonDays = 7;

    $daysLeft = $membershipEndsAt
        ? max(0, (int) now()->startOfDay()->diffInDays($membershipEndsAt->copy()->startOfDay(), false))
        : null;

    $isExpiringSoon = $daysLeft !== null && $daysLeft <= $expiringSoonDays;

    // home-6 / phone 360 px-2: a member without Premium gets the pink bar
    // that sells it. Admins browse these pages too but are not customers.
    $showUpsell = ! $membershipEndsAt && $bannerUser && $bannerUser->isMale() && ! $bannerUser->hasRole('admin');
@endphp

{{-- Geometry from home-5 / muž - záložky (1920): 50 px tall, full width of
     the content column, radius 8, 13 px Poppins 500 #505050, 20 px icon,
     close cross 16 px from the right edge. Phone (phone 360 px-1): the text
     wraps left-aligned next to the icon, cross top-right. --}}
@if($membershipEndsAt || $showUpsell)
    <div x-data="{ show: true }" x-show="show" x-cloak
         class="member-banner {{ $membershipEndsAt ? 'member-banner--premium' : 'member-banner--upsell' }}"
         style="background: {{ $membershipEndsAt ? '#E6FEE8' : '#FFE0E5' }};"
         role="status">
        <img src="{{ asset($membershipEndsAt ? 'images/icons/diamond.svg' : 'images/icons/OctagonAlert.svg') }}" alt="" class="member-banner__icon" />

        <span class="member-banner__text">
            @if($membershipEndsAt && $isExpiringSoon)
                {{ __('front.membership.expiring_soon', [
                    'days' => trans_choice('front.membership.period_days', $daysLeft, ['count' => $daysLeft]),
                ]) }}<a href="{{ route('account.member.membership.index') }}">{{ __('front.membership.extend_link') }}</a>
            @elseif($membershipEndsAt)
                {{-- muž - záložky*: the plain date, no link. --}}
                {{ __('front.membership.valid_until', ['date' => $membershipEndsAt->translatedFormat('j. n. Y')]) }}
            @else
                {{ __('front.membership.upsell') }}<a href="{{ route('account.member.membership.index') }}">{{ __('front.membership.upsell_link') }}</a>
            @endif
        </span>

        <button type="button" @click="show = false" class="member-banner__close"
                aria-label="{{ __('front.notifications.archive') }}">
            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1L9 9M9 1L1 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
    <style>
        .member-banner { position: relative; display: flex; align-items: flex-start; gap: 8px; width: 100%; min-height: 50px; border-radius: 8px; padding: 12px 40px 12px 16px; box-sizing: border-box; }
        .member-banner__icon { width: 20px; height: 20px; flex: 0 0 auto; margin-top: 2px; }
        .member-banner__text { font-family: 'Poppins', sans-serif; font-weight: 500; font-size: 13px; line-height: 21px; letter-spacing: -0.07px; color: #505050; }
        .member-banner__text a { color: #505050; text-decoration: underline; }
        .member-banner__close { position: absolute; right: 19px; top: 17px; width: 12px; height: 12px; display: flex; align-items: center; justify-content: center; }
        /* Pass 5: 12 px is too small to hit with a finger; an invisible 44 px
           touch area around the cross, the drawing stays as in the frame. */
        .member-banner__close::before { content: ''; position: absolute; inset: -16px; }
        .member-banner--premium .member-banner__close { color: #DD3888; }
        .member-banner--upsell .member-banner__close { color: #DD3888; }
        @media (min-width: 768px) {
            .member-banner { align-items: center; justify-content: center; padding: 0 40px 0 30px; }
            .member-banner__icon { margin-top: 0; }
            .member-banner__close { top: 50%; transform: translateY(-50%); }
            /* Desktop (home-5) draws the cross dark on green; phone 360 px-1 pink. */
            .member-banner--premium .member-banner__close { color: #505050; }
        }
    </style>
@endif
