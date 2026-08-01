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

<!-- Page Title -->
<div class="mb-4 md:mb-8 text-center">
    <h1 style="font-family:'Poppins',sans-serif;font-weight:700;font-size:36px;color:#5C2D62;">{{ __('front.account.member.girls_of_month') }}</h1>
    <p style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;padding-top:33px;padding-bottom:38px;">
        {{ __('front.account.member.girls_of_month_description') }}
    </p>
</div>
<hr class="mb-8 rounded-none">

<!-- Age Filter -->
<form method="GET" action="{{ route('account.member.girls-of-month') }}" class="flex items-end justify-center gap-4 flex-wrap" style="padding-bottom:60px;">
    <div>
        <label for="age_range" class="block mb-2" style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;padding-top:31px;">
            {{ __('front.account.member.archive_age_label') }}
        </label>
        <div class="relative" style="width:410px;max-width:100%;">
            <select id="age_range" name="age_range" class="appearance-none member-select" style="width:100%;height:60px;border-radius:8px;border:1px solid #E6E6E6;padding:0 66px 0 16px;font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:14px;color:#505050;background:#FFFFFF;">
                <option value="">{{ __('front.account.member.archive_age_label') }}</option>
                @foreach($ageRanges as $key => $bounds)
                    <option value="{{ $key }}" @selected($selectedAgeRange === $key)>{{ $key }} let</option>
                @endforeach
            </select>
            <span class="absolute pointer-events-none flex items-center justify-center" style="top:50%;right:5px;transform:translateY(-50%);width:50px;height:50px;background:#F2F2F2;border-radius:6px;">
                <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L5 4L9 1" stroke="#DD3888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>
    </div>

    <button type="submit" class="flex items-center justify-center gap-2" style="width:171px;height:60px;border-radius:8px;background:#DD3888;">
        <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#FFFFFF;">
            {{ __('front.account.member.archive_search') }}
        </span>
        <x-icons name="search" style="width:20px;height:20px;color:#FFFFFF;" />
    </button>
</form>
<hr class="mt-8 rounded-none" style="padding-bottom:100px;">

<h2 class="text-center" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:24px;color:#5C2D62;padding-bottom:56px;">
    {{ __('front.account.member.girls_of_month_results_heading') }}
</h2>

@if($profiles->count() > 0)
<div class="mt-6 grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
    @foreach($profiles as $profile)
        <x-profile-card :profile="$profile" />
    @endforeach
</div>

<div class="mt-8 flex justify-center">
    {{ $profiles->links('vendor.pagination.member') }}
</div>
@else
<div class="mt-6 bg-white rounded-lg border border-gray-200 p-8 md:p-12 text-center">
    <x-icons name="TriangleAlert" class="mx-auto mb-4" style="width:48px;height:48px;color:#DD3888;" />
    <p class="text-gray-500">{{ __('front.account.member.archive_empty') }}</p>
</div>
@endif
</div>
@endsection
