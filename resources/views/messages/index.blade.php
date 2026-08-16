@extends('layouts.app')

@section('title', __('front.messages.title'))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 pt-30">
    <style>
        /* Colours and typography follow the rest of the site. The page used
           `bg-primary` / `text-primary`, which are not generated utilities in
           this project — the classes resolved to nothing. */
        .msg-heading {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 32px;
            color: #5C2D62;
        }

        .msg-subheading {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #808080;
            margin-top: 4px;
        }

        .msg-list {
            background: #FFFFFF;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 15px 0 rgba(92, 45, 98, 0.08);
            margin-top: 24px;
        }

        .msg-row {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 18px 20px;
            text-decoration: none;
            border-bottom: 1px solid #EFEFEF;
            transition: background-color 150ms ease;
        }

        .msg-row:last-child { border-bottom: none; }
        .msg-row:hover { background: #FAF6FB; }

        /* Unread conversations are the ones that still need doing. */
        .msg-row-unread { background: #FDF3F8; }
        .msg-row-unread:hover { background: #FBE9F2; }

        .msg-row-body {
            flex: 1;
            min-width: 0;
        }

        .msg-row-top {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
        }

        .msg-row-name {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 16px;
            color: #333333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .msg-row-unread .msg-row-name { font-weight: 700; color: #5C2D62; }

        .msg-row-time {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #A6A6A6;
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* Two lines of the last message, so the list can be scanned without
           opening every thread. */
        .msg-row-preview {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            line-height: 20px;
            color: #707070;
            margin-top: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            overflow-wrap: anywhere;
        }

        .msg-row-unread .msg-row-preview { color: #333333; font-weight: 500; }

        .msg-row-you {
            color: #A6A6A6;
            font-weight: 400;
        }

        .msg-row-badge {
            flex-shrink: 0;
            align-self: center;
            min-width: 24px;
            height: 24px;
            padding: 0 8px;
            border-radius: 999px;
            background: #DD3888;
            color: #FFFFFF;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .msg-empty {
            background: #FFFFFF;
            border-radius: 15px;
            padding: 48px 24px;
            text-align: center;
            margin-top: 24px;
        }

        @media (max-width: 640px) {
            .msg-heading { font-size: 26px; }
            .msg-row { padding: 14px 16px; gap: 12px; }
        }
    </style>

    <h1 class="msg-heading">{{ __('front.messages.title') }}</h1>

    @if($conversations->isNotEmpty())
        <p class="msg-subheading">{{ __('front.messages.conversations_count', ['count' => $conversations->count()]) }}</p>
    @endif

    @if($conversations->isEmpty())
        <div class="msg-empty">
            <svg class="w-16 h-16 mx-auto mb-4" style="color:#D9D9D9;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <p style="font-family:'Poppins',sans-serif;font-size:18px;color:#505050;">{{ __('front.messages.no_messages_yet') }}</p>
            <p style="font-family:'Poppins',sans-serif;font-size:14px;color:#A6A6A6;margin-top:8px;">{{ __('front.messages.no_messages_desc') }}</p>
        </div>
    @else
        <div class="msg-list">
            @foreach($conversations as $conversation)
                @php
                    // A conversation partner whose account is gone must not take
                    // the whole inbox down.
                    $otherUser = $users[$conversation->other_user_id] ?? null;
                    $unreadCount = (int) $conversation->unread_count;
                    $lastMessage = $lastMessages[$conversation->last_message_id] ?? null;
                    $lastFromMe = $lastMessage && $lastMessage->from_user_id === Auth::id();
                    $lastAt = \Carbon\Carbon::parse($conversation->last_message_at);
                @endphp

                @if($otherUser)
                <a href="{{ route('messages.show', $otherUser) }}"
                   class="msg-row {{ $unreadCount > 0 ? 'msg-row-unread' : '' }}">
                    <x-user-avatar :user="$otherUser" :size="48" />

                    <div class="msg-row-body">
                        <div class="msg-row-top">
                            <h3 class="msg-row-name">{{ $otherUser->name }}</h3>
                            <span class="msg-row-time" title="{{ $lastAt->format('d.m.Y H:i') }}">
                                {{ $lastAt->diffForHumans() }}
                            </span>
                        </div>

                        @if($lastMessage)
                            <p class="msg-row-preview">
                                @if($lastFromMe)
                                    <span class="msg-row-you">{{ __('front.messages.you_prefix') }}</span>
                                @endif
                                {{ $lastMessage->message }}
                            </p>
                        @endif
                    </div>

                    @if($unreadCount > 0)
                        <span class="msg-row-badge"
                              title="{{ trans_choice(
                                  __('front.messages.unread_one') . '|' . __('front.messages.unread_few') . '|' . __('front.messages.unread_many'),
                                  $unreadCount
                              ) }}">{{ $unreadCount }}</span>
                    @endif
                </a>
                @endif
            @endforeach
        </div>
    @endif
</div>
@endsection
