@extends('layouts.member')

@section('member-width', 'wide')

@section('member-heading')
    <h1 class="member-title">{{ __('front.account.member.archive') }}</h1>
    <p class="member-lead">{{ __('front.account.member.archive_description') }}</p>
@endsection

@section('member-content')

<!-- Age Filter -->
{{-- muž - záložky-1 @1920: label 42 px under the divider, 410×60 select
     (2 px #E6E6E6 border), 20 px gap, 184×60 button; the pair starts 173 px
     into the column (it is not centred in the frame), second divider 60 px
     below the controls. --}}
<form method="GET" action="{{ route('account.member.archive') }}" class="archive-filter">
    <div class="archive-filter__field">
        <label for="age_range" class="archive-filter__label">
            {{ __('front.account.member.archive_age_label') }}
        </label>
        <div class="relative">
            <select id="age_range" name="age_range" class="appearance-none member-select archive-filter__select">
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

    <button type="submit" class="archive-filter__button">
        <span>{{ __('front.account.member.archive_search') }}</span>
        <x-icons name="search" class="archive-filter__icon" />
    </button>
</form>
<hr class="member-divider">

<h2 class="member-subtitle archive-results-title">
    {{ __('front.account.member.archive_results_heading') }}
</h2>

@if($profiles->count() > 0)
<div class="member-card-grid">
    @foreach($profiles as $profile)
        @php
            $maySeeProfileImage = (int) $profile->user_id === (int) $user->id
                || $user->hasAnyRole(['admin', 'super_admin']);
        @endphp
        <x-profile-card :profile="$profile" :showDetailLink="false" :imagesOverride="$maySeeProfileImage ? null : []" />
    @endforeach
</div>

<div class="mt-8 flex justify-center">
    {{ $profiles->links('vendor.pagination.member') }}
</div>
@else
<div class="member-empty bg-white rounded-lg border border-gray-200 p-8 md:p-12 text-center">
    {{-- x-icons strips the SVG's own size, so the box has to be sized by
         class; the inline style alone left a ~370 px triangle. --}}
    <x-icons name="TriangleAlert" class="member-empty-icon" />
    <p class="text-gray-500">{{ __('front.account.member.archive_empty') }}</p>
</div>
@endif

<style>
    .archive-filter { display: flex; flex-direction: column; align-items: stretch; gap: 16px; padding: 32px 0; }
    .archive-filter__label { display: block; margin-bottom: 4px; font-family: 'Poppins', sans-serif; font-weight: 400; font-size: 14px; line-height: 21px; color: #505050; padding-left: 5px; }
    .archive-filter__select { width: 100%; height: 60px; border-radius: 8px; border: 2px solid #E6E6E6; padding: 0 66px 0 20px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 14px; color: #505050; background: #FFFFFF; }
    .archive-filter__button { display: flex; align-items: center; justify-content: center; gap: 10px; height: 60px; border-radius: 8px; background: #DD3888; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #FFFFFF; }
    .archive-filter__button:hover { background: #CA2474; }
    .archive-filter__icon { width: 20px; height: 20px; color: #FFFFFF; }
    .archive-filter__icon svg { width: 100%; height: 100%; }
    @media (min-width: 768px) {
        .archive-filter { flex-direction: row; align-items: flex-end; justify-content: center; gap: 20px; padding: 37px 0 59px; }
        .archive-filter__field { width: 410px; max-width: 100%; }
        .archive-filter__button { width: 184px; flex: 0 0 auto; }
    }
    @media (min-width: 1280px) {
        .archive-filter { justify-content: flex-start; padding-left: 173px; }
    }
    @media (min-width: 768px) { .member-subtitle.archive-results-title { margin-top: 102px; margin-bottom: 0; } }
    @media (min-width: 1280px) { .archive-results-title + .member-card-grid { margin-top: 44px; } }
</style>
@endsection
