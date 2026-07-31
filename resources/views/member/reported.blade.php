@extends('layouts.member')

@section('member-content')
<!-- Page Title -->
<div class="mb-4 md:mb-8">
    <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('front.account.member.reported') }}</h1>
    <p class="mt-1 md:mt-2" style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;">
        {{ __('front.account.member.reported_description') }}
    </p>
</div>
<hr class="mb-8">

@if($reports->count() > 0)
<div class="flex flex-wrap gap-x-6 gap-y-8">
    @foreach($reports as $report)
    <div class="flex gap-4">
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
