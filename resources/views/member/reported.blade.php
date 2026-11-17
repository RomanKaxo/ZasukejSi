@extends('layouts.member')

@section('member-width', 'wide')

@section('member-heading')
    <h1 class="member-title">{{ __('front.account.member.reported') }}</h1>
    <p class="member-lead member-lead--narrow">{{ __('front.account.member.reported_description') }}</p>
@endsection

@section('member-content')

@if($reports->count() > 0)
<div class="reported-grid">
    @foreach($reports as $report)
    @php
        $maySeeProfileImage = ($report->profile->is_public && $report->profile->status === 'approved')
            || (int) $report->profile->user_id === (int) $user->id
            || $user->hasAnyRole(['admin', 'super_admin']);
    @endphp
    <div class="flex gap-0 reported-pair">
        <x-profile-card :profile="$report->profile" :isReported="true" :imagesOverride="$maySeeProfileImage ? null : []" />
        <x-reported-info-card :report="$report" :showProfileImage="$maySeeProfileImage" />
    </div>
    @endforeach
</div>
@else
<div class="bg-white rounded-lg border border-gray-200 p-8 md:p-12 text-center">
    <x-icons name="TriangleAlert" class="member-empty-icon" />
    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('front.account.member.reported') }}</h3>
    <p class="text-gray-500">{{ __('front.account.member.reported_description') }}</p>
</div>
@endif
<style>
    /* muž - záložky-3 @1920: two 440 px pairs (210 px card + 230 px case
       panel) flush with both edges of the 902 px column, first row 62 px under
       the divider, rows 26 px apart. phone 360 px-3: one pair per row. */
    /* The lead box in this frame is narrower and tighter than on Archiv. */
    @media (min-width: 768px) { .member-lead.member-lead--narrow { max-width: 780px; line-height: 24px; margin-top: 34px; margin-bottom: 9px; } }
    .reported-grid { display: grid; grid-template-columns: 1fr; justify-items: center; row-gap: 26px; margin-top: 32px; }
    .reported-grid .reported-pair > .reported-info-card { flex: 0 0 auto; }
    @media (min-width: 768px) {
        .reported-grid .reported-pair > .reported-profile-card { width: 210px !important; flex: 0 0 210px; }
        .reported-grid .reported-pair > .reported-info-card { width: 230px !important; }
    }
    @media (min-width: 1280px) {
        .reported-grid { grid-template-columns: repeat(2, 440px); justify-content: space-between; margin-top: 61px; }
    }
    /* muž - záložky-3, case panel: icon ink 35 px under the top, text and
       pills on a 171 px column (29 px sides), title ink 23 px under the
       icon, reason clamped to 7 lines of 22 px ending in "…" (the clamp
       never applied: md:block replaced the -webkit-box it needs), pills 7 px
       apart, button 18 px under them and 28 px above the bottom. */
    @media (min-width: 768px) {
        .reported-grid .reported-info-card { padding: 32px 29px 28px !important; }
        .reported-grid .reported-info-card-title { margin-top: 17px !important; }
        .reported-grid .reported-info-card p { display: -webkit-box !important; -webkit-box-orient: vertical; -webkit-line-clamp: 7; line-clamp: 7; overflow: hidden; line-height: 22px !important; }
        .reported-grid .reported-info-card-badges { gap: 7px; }
        .reported-grid .reported-info-card-button { margin-top: 11px; }
        /* Pass 4: the pills always start 283 px under the panel top and the
           button sits 26.5 px above its bottom (Angela's two pills stay up
           under the text in the frame, they did not slide down to the
           button); the button keeps the bottom. */
        .reported-grid .reported-info-card-badges { position: absolute; top: 283px; left: 29px; right: 29px; bottom: 27px; margin-top: 0 !important; justify-content: flex-start; }
        .reported-grid .reported-info-card-button { margin-top: auto !important; }
    }
</style>
@endsection
