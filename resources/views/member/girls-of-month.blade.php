@extends('layouts.member')

@section('member-width', 'wide')

@section('member-heading')
    <h1 class="member-title">{{ __('front.account.member.girls_of_month') }} TOP 10</h1>
@endsection

@section('member-content')
    @if($profiles->isNotEmpty())
        <div class="member-card-grid member-card-grid--top">
            @foreach($profiles as $profile)
                <x-profile-card :profile="$profile" :rating-decimals="2" />
            @endforeach
        </div>
    @else
        <div class="member-empty bg-white rounded-lg border border-gray-200 p-8 md:p-12 text-center">
            <p class="text-gray-500">{{ __('front.account.member.girls_of_month_empty') }}</p>
        </div>
    @endif
@endsection
