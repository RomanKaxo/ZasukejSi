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
    $membershipEndsAt = auth()->user()?->membershipEndsAt();

    // Below this the bar counts down instead of naming a date.
    $expiringSoonDays = 7;

    $daysLeft = $membershipEndsAt
        ? max(0, (int) now()->startOfDay()->diffInDays($membershipEndsAt->copy()->startOfDay(), false))
        : null;

    $isExpiringSoon = $daysLeft !== null && $daysLeft <= $expiringSoonDays;
@endphp

@if($membershipEndsAt)
    <div x-data="{ show: true }" x-show="show" x-cloak
         class="relative flex items-center justify-center gap-3 mx-auto mb-6"
         style="width:902px;max-width:100%;min-height:50px;background:#E6FEE8;border-radius:8px;padding:8px 40px 8px 16px;box-sizing:border-box;">
        <img src="{{ asset('images/icons/diamond.svg') }}" alt="" style="width:20px;height:20px;flex:0 0 auto;" />

        <span style="font-family:'Poppins',sans-serif;font-weight:500;font-size:13px;color:#505050;">
            @if($isExpiringSoon)
                {{ __('front.membership.expiring_soon', [
                    'days' => trans_choice('front.membership.period_days', $daysLeft, ['count' => $daysLeft]),
                ]) }}
            @else
                {{ __('front.membership.valid_until', ['date' => $membershipEndsAt->translatedFormat('j. n. Y')]) }}
                &ndash;
            @endif
            <a href="{{ route('account.member.membership.index') }}"
               style="color:#505050;text-decoration:underline;">{{ __('front.membership.extend_link') }}</a>
        </span>

        <button type="button" @click="show = false"
                class="absolute right-4 top-1/2 -translate-y-1/2"
                aria-label="{{ __('front.notifications.archive') }}">
            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1L9 9M9 1L1 9" stroke="#DD3888" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
@endif
