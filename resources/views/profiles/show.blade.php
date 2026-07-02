@extends('layouts.app')

@section('title', $profile->display_name . ' - ' . __('front.profiles.detail'))

@section('content')
<x-profile-detail :profile="$profile" />
@endsection