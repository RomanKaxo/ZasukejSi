@extends('layouts.member')

@section('member-content')
<!-- Page Title -->
<div class="mb-4 md:mb-6">
    <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('front.account.member.ratings') }}</h1>
    <p class="mt-1 md:mt-2 text-sm text-gray-600">{{ __('front.account.member.ratings_description') }}</p>
</div>

<!-- Ratings Component -->
<livewire:member-ratings />
@endsection
