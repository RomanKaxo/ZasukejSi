@extends('layouts.account')

@section('title', __('front.account.sidebar.photos'))

@php
    $activeItem = 'photos';
@endphp

@section('account-content')
    @include('account.partials.page-header', ['title' => __('front.account.sidebar.photos'), 'ruleGap' => 'mb-[42px]'])

    @livewire('photos-manager')
@endsection
