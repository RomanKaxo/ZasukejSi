@extends('layouts.app')

@section('title', __('front.messages.conversation_with') . ' ' . $user->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 pt-30">
    <style>
        /* The outgoing bubble was `bg-primary text-white`. Neither `bg-primary`
           nor `text-primary` is a generated utility in this project, so the
           background never applied and the sender's own messages were white
           text on white — invisible. Colours are written out here. */
        .msg-thread-head {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }

        .msg-back {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(92, 45, 98, 0.10);
            color: #5C2D62;
        }

        .msg-back:hover { background: #FAF6FB; }

        .msg-thread-name {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 22px;
            color: #5C2D62;
            margin: 0;
        }

        .msg-thread-link {
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #DD3888;
            text-decoration: underline;
        }

        .msg-thread {
            background: #FFFFFF;
            border-radius: 15px;
            padding: 24px;
            margin-bottom: 20px;
            max-height: 560px;
            overflow-y: auto;
            box-shadow: 0 15px 15px 0 rgba(92, 45, 98, 0.08);
        }

        /* A dated divider, so a long thread reads as a timeline rather than one
           undifferentiated run of bubbles. */
        .msg-day {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0 14px;
        }

        .msg-day:first-child { margin-top: 0; }
        .msg-day::before,
        .msg-day::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #EFEFEF;
        }

        .msg-day span {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #A6A6A6;
            white-space: nowrap;
        }

        .msg-line {
            display: flex;
            margin-bottom: 14px;
        }

        .msg-line-mine { justify-content: flex-end; }

        .msg-bubble-wrap { max-width: 70%; min-width: 0; }

        .msg-bubble {
            border-radius: 14px;
            padding: 12px 16px;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            line-height: 22px;
            /* Line breaks the sender typed are part of the message. */
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .msg-bubble-mine {
            background: #DD3888;
            color: #FFFFFF;
            border-bottom-right-radius: 4px;
        }

        .msg-bubble-theirs {
            background: #F2F2F2;
            color: #333333;
            border-bottom-left-radius: 4px;
        }

        .msg-meta {
            font-family: 'Poppins', sans-serif;
            font-size: 11px;
            color: #A6A6A6;
            margin-top: 4px;
            display: flex;
            gap: 6px;
        }

        .msg-line-mine .msg-meta { justify-content: flex-end; }

        .msg-compose {
            background: #FFFFFF;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 15px 15px 0 rgba(92, 45, 98, 0.08);
        }

        .msg-compose textarea {
            width: 100%;
            border-radius: 8px;
            border: 2px solid #E6E6E6;
            padding: 14px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            line-height: 22px;
            color: #333333;
            resize: vertical;
        }

        .msg-compose textarea:focus {
            outline: none;
            border-color: #DD3888;
        }

        .msg-compose-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 12px;
        }

        .msg-compose-hint {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #A6A6A6;
        }

        .msg-send {
            padding: 12px 28px;
            border-radius: 8px;
            background: #DD3888;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 16px;
            color: #FFFFFF;
            cursor: pointer;
            transition: background-color 150ms ease, transform 150ms ease;
        }

        .msg-send:hover { background: #c4286f; transform: translateY(-1px); }

        .msg-flash {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #2F6F3E;
            background: #EAF6EC;
            border: 2px solid #B7E0C0;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 16px;
        }

        @media (max-width: 640px) {
            .msg-thread { padding: 16px; }
            .msg-bubble-wrap { max-width: 85%; }
            .msg-compose-foot { flex-direction: column; align-items: stretch; }
            .msg-send { width: 100%; }
        }
    </style>

    <div class="msg-thread-head">
        <a href="{{ route('messages.index') }}" class="msg-back" aria-label="{{ __('front.messages.back_to_messages') }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>

        <x-user-avatar :user="$user" :size="44" />

        <div style="min-width:0;">
            <h1 class="msg-thread-name">{{ $user->name }}</h1>
            @if($user->profile)
                <a href="{{ route('profiles.show', $user->profile) }}" class="msg-thread-link">
                    {{ __('front.messages.view_profile') }}
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="msg-flash" role="status">{{ session('success') }}</div>
    @endif

    <div class="msg-thread" id="msg-thread">
        @php $previousDay = null; @endphp

        @forelse($messages as $message)
            @php
                $mine = $message->from_user_id === Auth::id();
                $day = $message->created_at->toDateString();
                $dayLabel = match (true) {
                    $message->created_at->isToday() => __('front.messages.today'),
                    $message->created_at->isYesterday() => __('front.messages.yesterday'),
                    default => $message->created_at->translatedFormat('j. F Y'),
                };
            @endphp

            @if($day !== $previousDay)
                <div class="msg-day"><span>{{ $dayLabel }}</span></div>
                @php $previousDay = $day; @endphp
            @endif

            <div class="msg-line {{ $mine ? 'msg-line-mine' : '' }}">
                <div class="msg-bubble-wrap">
                    <div class="msg-bubble {{ $mine ? 'msg-bubble-mine' : 'msg-bubble-theirs' }}">{{ $message->message }}</div>
                    <div class="msg-meta">
                        <span>{{ $message->created_at->format('H:i') }}</span>
                        @if($mine)
                            {{-- Whether the other side has seen it: the reason
                                 people re-send the same message. --}}
                            <span>· {{ $message->read_at ? __('front.messages.read') : __('front.messages.delivered') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:32px 0;font-family:'Poppins',sans-serif;color:#A6A6A6;">
                {{ __('front.messages.no_messages_conversation') }}
            </div>
        @endforelse
    </div>

    <div class="msg-compose">
        <form action="{{ route('messages.store', $user) }}" method="POST" id="msg-form">
            @csrf
            <textarea
                name="message"
                rows="3"
                placeholder="{{ __('front.messages.type_message') }}"
                required
            >{{ old('message') }}</textarea>

            @error('message')
                <p style="font-family:'Poppins',sans-serif;font-size:12px;color:#DD3888;margin-top:6px;">{{ $message }}</p>
            @enderror

            <div class="msg-compose-foot">
                <span class="msg-compose-hint">{{ __('front.messages.send_hint') }}</span>
                <button type="submit" class="msg-send">{{ __('front.messages.send_message') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Addressed by id: the previous version matched on the Tailwind class
        // that set the height, so changing the height broke the scrolling.
        const thread = document.getElementById('msg-thread');
        if (thread) {
            thread.scrollTop = thread.scrollHeight;
        }

        const form = document.getElementById('msg-form');
        const field = form ? form.querySelector('textarea') : null;
        if (form && field) {
            field.addEventListener('keydown', function (event) {
                if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                    form.requestSubmit();
                }
            });
        }
    });
</script>
@endsection
