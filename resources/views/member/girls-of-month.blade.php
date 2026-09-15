@extends('layouts.member')

@section('member-content')
<div class="w-[310px] md:w-auto mx-auto">
    <div class="mb-4 md:mb-8 text-center">
        <h1 style="font-family:'Poppins',sans-serif;font-weight:700;font-size:36px;color:#5C2D62;">{{ __('front.account.member.girls_of_month') }} TOP 10</h1>
    </div>
    <hr class="mb-8 rounded-none">

    @if($profiles->isNotEmpty())
        <div class="mt-6 grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
            @foreach($profiles as $profile)
                <x-profile-card :profile="$profile" />
            @endforeach
        </div>
    @else
        <div class="mt-6 bg-white rounded-lg border border-gray-200 p-8 md:p-12 text-center">
            <p class="text-gray-500">{{ __('front.account.member.girls_of_month_empty') }}</p>
        </div>
    @endif
</div>
@endsection
