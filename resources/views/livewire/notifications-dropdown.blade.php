{{--
    The header notification bell.

    The trigger below reproduces the exact geometry the navbar used for its
    static placeholder (60x60 box, 1px #DD3888 border, 8px radius, 26x26 icon,
    26x26 green badge pinned to the corner) so swapping the placeholder for this
    working component changes behaviour without moving a single pixel.

    `wire:poll` keeps the badge honest without a page reload.
--}}
<div class="relative" x-data="{ notificationsOpen: false }" wire:poll.30s>
    <button
        type="button"
        @click="notificationsOpen = !notificationsOpen"
        class="relative block w-[60px] h-[60px]"
        :aria-expanded="notificationsOpen ? 'true' : 'false'"
        aria-haspopup="true"
        title="{{ __('front.nav.notifications') }}"
    >
        <span class="w-[60px] h-[60px] border border-[#DD3888] rounded-[8px] flex items-center justify-center">
            <img src="{{ asset('images/icons/bell.svg') }}" class="w-[26px] h-[26px]" alt="{{ __('front.nav.notifications') }}">
        </span>

        @if($this->unreadCount > 0)
            <span class="absolute -top-1 -right-1 w-[26px] h-[26px] bg-[#00B80F] rounded-full flex items-center justify-center font-bold text-[10px] text-white"
                  style="font-family: 'Poppins', sans-serif;">
                {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="notificationsOpen"
         x-cloak
         @click.outside="notificationsOpen = false"
         @keydown.escape.window="notificationsOpen = false"
         x-transition
         class="fixed left-4 right-4 top-24 lg:absolute lg:left-auto lg:right-0 lg:top-full w-auto lg:w-80 bg-white rounded-lg z-50 max-h-96 overflow-y-auto border border-gray-200 shadow-lg mt-2">

        @if($this->notifications->isNotEmpty() && $this->unreadCount > 0)
            <div class="flex justify-end px-4 pt-3">
                <button type="button" wire:click="markAllAsRead" class="text-xs text-primary hover:underline">
                    {{ __('front.notifications.mark_all_read') }}
                </button>
            </div>
        @endif

        @if($this->notifications->isEmpty())
            <div class="p-6 text-center text-gray-500">
                {{ __('front.notifications.no_notifications') }}
            </div>
        @else
            @foreach($this->notifications as $notification)
                @php
                    // Global notifications are a single shared row, so "read"
                    // has to come from this user's pivot state — reading
                    // $notification->read_at would let one user mark it read for
                    // everyone. Notification::isReadBy() handles both kinds.
                    $isRead = $notification->isReadBy(auth()->id());

                    $accent = match ($notification->type) {
                        'success' => '#00B80F',
                        'warning' => '#FFB700',
                        'danger' => '#DD3888',
                        default => '#5C2D62',
                    };
                @endphp

                <div class="flex items-start gap-3 p-4 border-t border-gray-50 first:border-t-0 {{ $isRead ? '' : 'bg-primary/5' }} hover:bg-gray-50 transition-colors"
                     wire:key="notification-{{ $notification->id }}">

                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full"
                          style="background: {{ $accent }}; {{ $isRead ? 'opacity:0.35;' : '' }}"
                          aria-hidden="true"></span>

                    {{-- Clicking the body marks it read; archiving stays a separate action. --}}
                    <button type="button"
                            wire:click="markAsRead({{ $notification->id }})"
                            class="flex-1 pr-2 text-left">
                        <h4 class="font-medium text-gray-900 text-sm {{ $isRead ? 'font-normal' : '' }}">{{ $notification->title }}</h4>
                        <p class="text-xs text-gray-600 mt-1">{{ $notification->message }}</p>
                        <p class="text-xs text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                    </button>

                    <button type="button"
                            wire:click="archive({{ $notification->id }})"
                            class="text-gray-400 hover:text-primary flex-shrink-0"
                            title="{{ __('front.notifications.archive') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endforeach
        @endif

        <div class="p-3 border-t border-gray-100 text-center">
            <a href="{{ route('notifications.archived') }}" class="text-sm text-primary hover:underline">
                {{ __('front.notifications.view_archived') }}
            </a>
        </div>
    </div>
</div>
