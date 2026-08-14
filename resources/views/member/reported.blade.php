@extends('layouts.member')

@section('member-content')
<!-- Page Title -->
<div class="mb-4 md:mb-8 text-center">
    <h1 style="font-family:'Poppins',sans-serif;font-weight:700;font-size:36px;color:#5C2D62;">{{ __('front.account.member.reported') }}</h1>
    <p style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;padding-top:33px;padding-bottom:38px;">
        {{ __('front.account.member.reported_description') }}
    </p>
</div>
<hr class="mb-8 rounded-none">

@if($reports->count() > 0)
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
    @foreach($reports as $report)
    <div class="flex gap-0 reported-pair">
        <x-profile-card :profile="$report->profile" :isReported="true" />
        <x-reported-info-card :report="$report" />
    </div>
    @endforeach
</div>
@else
<div class="bg-white rounded-lg border border-gray-200 p-8 md:p-12 text-center">
    <x-icons name="TriangleAlert" class="mx-auto mb-4" style="width:48px;height:48px;color:#DD3888;" />
    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('front.account.member.reported') }}</h3>
    <p class="text-gray-500">{{ __('front.account.member.reported_description') }}</p>
</div>
@endif
@endsection
