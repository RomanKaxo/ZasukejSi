<div class="mb-8">
    <h1 class="text-2xl md:text-4xl font-semibold text-secondary py-6 text-center">
        {{ $archived ? __('front.notifications.archived_title') : __('front.nav.notifications') }}
    </h1>
    <hr>
    <nav class="flex flex-wrap justify-center gap-4 mt-4" aria-label="{{ __('front.nav.notifications') }}">
        <a href="{{ route('notifications.index') }}"
           class="text-primary hover:underline {{ ! $archived ? 'font-semibold' : '' }}"
           @if(! $archived) aria-current="page" @endif>
            {{ __('front.notifications.view_active') }}
        </a>
        <a href="{{ route('notifications.archived') }}"
           class="text-primary hover:underline {{ $archived ? 'font-semibold' : '' }}"
           @if($archived) aria-current="page" @endif>
            {{ __('front.notifications.view_archived') }}
        </a>
    </nav>
</div>

@if(session('success'))
    <p role="status" class="mb-6 rounded-lg p-4 bg-green-50 border border-green-200 text-green-800">
        {{ session('success') }}
    </p>
@endif

<div class="bg-white rounded-lg border border-gray-200">
    @if($notifications->isEmpty())
        <div class="p-8 text-center text-gray-500">
            <p class="text-lg">{{ $archived ? __('front.notifications.no_archived') : __('front.notifications.no_notifications') }}</p>
            @if($archived)
                <p class="text-sm mt-2">{{ __('front.notifications.archived_hint') }}</p>
            @endif
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($notifications as $notification)
                @php
                    $isRead = $notification->isReadBy(auth()->id());
                    $archivedAt = $notification->is_global
                        ? ($notification->userStates->firstWhere('user_id', auth()->id())?->archived_at ?? $notification->archived_at)
                        : $notification->archived_at;
                @endphp
                <article class="p-4 {{ ! $archived && ! $isRead ? 'bg-primary/5' : '' }}"
                         data-notification-id="{{ $notification->id }}">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block w-2 h-2 rounded-full shrink-0"
                              style="background:{{ $notification->typeColor() }}" aria-hidden="true"></span>
                        <h2 class="text-gray-900 {{ ! $isRead ? 'font-semibold' : 'font-medium' }}">{{ $notification->title }}</h2>
                    </div>
                    <p class="text-gray-600 mb-2 break-words">{{ $notification->message }}</p>
                    <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                        <span>{{ __('front.notifications.received') }}: {{ $notification->created_at->format('d.m.Y H:i') }}</span>
                        @if($archived)
                            <span>{{ __('front.notifications.archived') }}: {{ $archivedAt?->format('d.m.Y H:i') }}</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-4 mt-3">
                        @if(! $archived)
                            @if(! $isRead)
                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit" class="text-sm text-primary hover:underline">
                                        {{ __('front.notifications.mark_read') }}
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('notifications.archive', $notification) }}">
                                @csrf
                                <button type="submit" class="text-sm text-primary hover:underline">
                                    {{ __('front.notifications.archive') }}
                                </button>
                            </form>
                        @elseif(! $notification->is_global)
                            {{-- Shared notices are retained in history. Only a personal row can be permanently deleted. --}}
                            <form method="POST" action="{{ route('notifications.delete', $notification) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:underline">
                                    {{ __('front.notifications.delete_permanently') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        @if($notifications->hasPages())
            <div class="p-4 border-t border-gray-100">{{ $notifications->links() }}</div>
        @endif
    @endif
</div>

<div class="mt-6 text-center">
    <a href="{{ route($archived ? 'notifications.index' : 'account.dashboard') }}" class="text-primary hover:underline">
        ← {{ __('front.notifications.go_back') }}
    </a>
</div>
