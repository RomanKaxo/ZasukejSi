@extends('layouts.member')

@section('member-content')
<div class="w-[310px] md:w-auto mx-auto">
<!-- Premium Membership Banner -->
<div x-data="{ show: true }" x-show="show" x-cloak class="relative flex items-center justify-center gap-3 mx-auto mb-6" style="width:902px;max-width:100%;height:50px;background:#E6FEE8;border-radius:8px;padding:0 16px;box-sizing:border-box;">
    <img src="{{ asset('images/icons/diamond.svg') }}" alt="" style="width:20px;height:20px;" />
    <span style="font-family:'Poppins',sans-serif;font-weight:500;font-size:13px;color:#505050;">
        Vaše Premium členství platí do 12. 12. 2025
    </span>
    <button type="button" @click="show = false" class="absolute right-4 top-1/2 -translate-y-1/2" aria-label="Zavřít">
        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 1L9 9M9 1L1 9" stroke="#000000" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
    </button>
</div>

<!-- Welcome Title -->
<div class="mb-4 md:mb-8 text-center">
    <h1 style="font-family:'Poppins',sans-serif;font-weight:700;font-size:36px;">
        <span style="color:#D4D4D4;">{{ __('front.account.member.welcome') }},</span>
        <span style="color:#5C2D62;">{{ $user->name }}</span>
    </h1>
</div>
<hr class="mb-8 rounded-none">

<h2 class="text-center" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:24px;color:#5C2D62;padding-bottom:32px;">
    {{ __('front.account.member.ratings_heading') }}
</h2>

<!-- Ratings Component -->
<livewire:member-ratings />
</div>
@endsection
