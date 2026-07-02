@extends('layouts.account')

@section('title', __('front.notifications.archived_title'))

@php
    $activeItem = 'notifications';
@endphp

@section('account-content')
    <div class="mb-8">
        <h1 class="text-4xl font-semibold text-secondary py-6 text-center">
            {{ __('front.notifications.archived_title') }}
        </h1>
        <hr>
    </div>

    {{-- Success Messages --}}
    @if (session('success'))
        <div class="mb-6 rounded-lg p-4 bg-green-50 border border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg border border-gray-200">
        @if($notifications->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-lg">{{ __('front.notifications.no_archived') }}</p>
                <p class="text-sm mt-2">{{ __('front.notifications.archived_hint') }}</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($notifications as $notification)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 pr-4">
                                <div class="flex items-center gap-2 mb-1">
                                    @if($notification->type === 'success')
                                        <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                                    @elseif($notification->type === 'warning')
                                        <span class="inline-block w-2 h-2 rounded-full bg-yellow-500"></span>
                                    @elseif($notification->type === 'danger')
                                        <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                                    @else
                                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                                    @endif
                                    <h4 class="font-medium text-gray-900">{{ $notification->title }}</h4>
                                </div>
                                <p class="text-gray-600 mb-2">{{ $notification->message }}</p>
                                <div class="flex items-center gap-4 text-xs text-gray-400">
                                    <span>{{ __('front.notifications.received') }}: {{ $notification->created_at->format('d.m.Y H:i') }}</span>
                                    <span>{{ __('front.notifications.archived') }}: {{ $notification->archived_at->format('d.m.Y H:i') }}</span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('notifications.delete', $notification) }}" class="flex-shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-500 p-2" title="{{ __('front.notifications.delete_permanently') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($notifications->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>

    <div class="mt-6 text-center">
        <a href="{{ url()->previous() }}" class="text-primary hover:underline">
            ← {{ __('front.notifications.go_back') }}
        </a>
    </div>
@endsection
