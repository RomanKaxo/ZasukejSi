@extends('layouts.account')

@section('title', __('front.account.services.page_title'))

@php
    $activeItem = 'services';
@endphp

@section('account-content')
    @include('account.partials.page-header', ['title' => __('front.account.services.page_title'), 'ruleGap' => 'mb-[42px]'])

    @livewire('services-manager')
@endsection
