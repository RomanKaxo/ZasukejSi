@extends('layouts.app')

@section('title', __('front.messages.conversation_with') . ' ' . $user->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 pt-30">
    <!-- Header -->
    <div class="mb-6 flex items-center">
        <a href="{{ route('messages.index') }}" class="text-primary hover:text-primary/80 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center text-lg font-bold">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="ml-3">
                <h1 class="text-xl font-bold text-gray-900">{{ $user->name }}</h1>
            </div>
        </div>
    </div>

    <!-- Messages Container -->
    <div class="bg-white rounded-lg p-6 mb-6 max-h-[500px] overflow-y-auto space-y-4">
        @forelse($messages as $message)
            <div class="flex {{ $message->from_user_id === Auth::id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs lg:max-w-md">
                    <div class="rounded-lg p-4 {{ $message->from_user_id === Auth::id() ? 'bg-primary text-white' : 'bg-gray-100 text-gray-900' }}">
                        <p class="text-sm break-words">{{ $message->message }}</p>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 {{ $message->from_user_id === Auth::id() ? 'text-right' : '' }}">
                        {{ $message->created_at->translatedFormat('M d, H:i') }}
                    </p>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-8">
                <p>{{ __('front.messages.no_messages_conversation') }}</p>
            </div>
        @endforelse
    </div>

    <!-- Message Input -->
    <div class="bg-white rounded-lg p-6">
        <form action="{{ route('messages.store', $user) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <textarea 
                    name="message" 
                    rows="3" 
                    class="w-full rounded-lg p-4 border-2 border-gray-200 focus:border-primary focus:ring-0 resize-none"
                    placeholder="{{ __('front.messages.type_message') }}"
                    required
                >{{ old('message') }}</textarea>
                
                @error('message')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">
                        {{ __('front.messages.send_message') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-scroll to bottom of messages
    document.addEventListener('DOMContentLoaded', function() {
        const messagesContainer = document.querySelector('.max-h-\\[500px\\]');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    });
</script>
@endsection
