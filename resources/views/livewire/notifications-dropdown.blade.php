<div class="relative" x-data="{ notificationsOpen: false }" wire:poll.3s>
    <button @click="notificationsOpen = !notificationsOpen" class="btn nav-button bg-gray-50 !px-4 !py-4 !border-1 !text-primary !border-primary relative rounded-lg" title="{{ __('front.nav.notifications') }}">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if($this->unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center text-xs">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>
    
    <div x-show="notificationsOpen" @click.outside="notificationsOpen = false" x-transition 
         class="fixed left-4 right-4 top-38 lg:absolute lg:left-auto lg:right-0 lg:top-auto mt-2 w-auto lg:w-80 bg-white rounded-lg z-50 max-h-96 overflow-y-auto border border-gray-200 shadow-lg">
        
        @if($this->notifications->isEmpty())
            <div class="p-6 text-center text-gray-500">
                {{ __('front.notifications.no_notifications') }}
            </div>
        @else
            @foreach($this->notifications as $notification)
                <div class="p-4 {{ !$notification->read_at ? 'bg-primary/5' : '' }} hover:bg-gray-50 transition-colors" wire:key="notification-{{ $notification->id }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 pr-2">
                            <h4 class="font-medium text-gray-900 text-sm">{{ $notification->title }}</h4>
                            <p class="text-xs text-gray-600 mt-1">{{ $notification->message }}</p>
                            <p class="text-xs text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        <button wire:click="archive({{ $notification->id }})" class="text-gray-400 hover:text-primary flex-shrink-0" title="{{ __('front.notifications.archive') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        @endif
        
        <!-- Link to Archived Notifications -->
        <div class="p-3 border-t border-gray-100 text-center">
            <a href="{{ route('notifications.archived') }}" class="text-sm text-primary hover:underline">
                {{ __('front.notifications.view_archived') }}
            </a>
        </div>
    </div>
</div>
