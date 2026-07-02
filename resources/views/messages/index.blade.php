@extends('layouts.app')

@section('title', __('front.messages.title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 pt-30">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('front.messages.title') }}</h1>

    @if($conversations->isEmpty())
        <div class="bg-white rounded-lg p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <p class="text-gray-500 text-lg">{{ __('front.messages.no_messages_yet') }}</p>
            <p class="text-gray-400 mt-2">{{ __('front.messages.no_messages_desc') }}</p>
        </div>
    @else
        <div class="bg-white rounded-lg">
            @foreach($conversations as $conversation)
                @php
                    $otherUser = $users[$conversation->other_user_id];
                    $unreadCount = \App\Models\Message::where('from_user_id', $otherUser->id)
                        ->where('to_user_id', Auth::id())
                        ->whereNull('read_at')
                        ->count();
                @endphp
                <a href="{{ route('messages.show', $otherUser) }}" 
                   class="flex items-center p-6 hover:bg-gray-50 transition-colors {{ !$loop->last ? 'border-b' : '' }}">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center text-lg font-bold">
                        {{ substr($otherUser->name, 0, 1) }}
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">{{ $otherUser->name }}</h3>
                            <span class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($conversation->last_message_at)->diffForHumans() }}
                            </span>
                        </div>
                        @if($unreadCount > 0)
                            <div class="flex items-center mt-1">
                                <span class="bg-primary text-white text-xs px-2 py-1 rounded-full">
                                    {{ $unreadCount }} {{ __('front.messages.new') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
