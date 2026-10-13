@extends($notificationLayout)

@section('title', __('front.notifications.archived_title'))

@php $activeItem = 'notifications'; @endphp

@section($notificationContentSection)
    @include('account.notifications.list', ['archived' => true])
@endsection
