{{--
    "Vaše Premium členství platí do …" — the green bar the design puts above
    every member page.

    This markup used to be pasted into five member views, each carrying the
    literal date 12. 12. 2025, so every member was told the same thing
    regardless of reality — and nothing backed it, because subscriptions were
    keyed on `profile_id` and a member has no profile.

    It now renders only for a member who actually holds a membership, with the
    real end date from User::membershipEndsAt(). Geometry is unchanged.
--}}
@php
    $membershipEndsAt = auth()->user()?->membershipEndsAt();
@endphp

@if($membershipEndsAt)
    <div x-data="{ show: true }" x-show="show" x-cloak
         class="relative flex items-center justify-center gap-3 mx-auto mb-6"
         style="width:902px;max-width:100%;height:50px;background:#E6FEE8;border-radius:8px;padding:0 16px;box-sizing:border-box;">
        <img src="{{ asset('images/icons/diamond.svg') }}" alt="" style="width:20px;height:20px;" />

        <span style="font-family:'Poppins',sans-serif;font-weight:500;font-size:13px;color:#505050;">
            {{ __('front.membership.valid_until', ['date' => $membershipEndsAt->translatedFormat('j. n. Y')]) }}
        </span>

        <button type="button" @click="show = false"
                class="absolute right-4 top-1/2 -translate-y-1/2"
                aria-label="{{ __('front.notifications.archive') }}">
            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1L9 9M9 1L1 9" stroke="#000000" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
@endif
