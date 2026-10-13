@extends($notificationLayout)

@section('title', __('front.nav.notifications'))

@php $activeItem = 'notifications'; @endphp

@section($notificationContentSection)
    @include('account.notifications.list', ['archived' => false])
@endsection
