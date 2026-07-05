@props(['profile', 'photoStatusLabel', 'messageRouteAvailable', 'registerRouteAvailable'])

<aside class="vip-profile-panel">
    <div class="vip-profile-status-bar" style="display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; gap:6px; align-items:center;">
            <span class="vip-profile-status-pill vip-profile-status-pill--primary">
                <img src="{{ asset('images/icons/star.svg') }}" alt="star" style="width: 18px; height: 18px; margin-right: 6px;">
                VIP PROFIL
            </span>
            <span class="vip-profile-status-pill vip-profile-status-pill--verification">
                <img src="{{ asset('images/icons/CameraOff.svg') }}" alt="camera-off" style="width: 18px; height: 18px; margin-right: 4px;">
                {{ $photoStatusLabel }}
            </span>
        </div>
        
        <div class="vip-profile-favorite-mobile" style="display:none;">
            @auth
                @if(auth()->user()->isMale())
                    @livewire('favorite-button', ['profile' => $profile], key('favorite-'.$profile->id))
                @else
                    <button type="button" class="vip-profile-static-favorite">
                        <img src="{{ asset('images/icons/heart.svg') }}" alt="" aria-hidden="true">
                    </button>
                @endif
            @else
                <button type="button" class="vip-profile-static-favorite">
                    <img src="{{ asset('images/icons/heart.svg') }}" alt="" aria-hidden="true">
                </button>
            @endauth
        </div>
    </div>

    <h1 class="vip-profile-name">{{ $profile->display_name ?? 'Alexandrina' }}</h1>

    <div class="vip-profile-rating-summary">
        <strong>{{ __('front.profiles.list.rating') }}</strong>
        <span class="vip-profile-rating-icons">
            @if($profile->getTotalRatings() > 0)
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="color:#FFC107;">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span>{{ number_format($profile->getAverageRating(), 1) }}</span>
            @else
                <x-icons name="lock" class="inline-block" style="width:18px;height:18px;color:#FF4DA6;" />
            @endif
        </span>
    </div>

    <div class="vip-profile-meta-location">
        <img src="{{ asset('images/icons/location.svg') }}" alt="" aria-hidden="true">
        <span>{{ $profile->city ?? 'Jihomoravský kraj' }}</span>
    </div>

    <div class="vip-profile-meta-table">
        <div class="vip-profile-meta-row">
            <span class="vip-profile-meta-label">Věk</span>
            <span class="vip-profile-meta-value">{{ $profile->age ?? '19' }} let</span>
        </div>
        <div class="vip-profile-meta-row">
            <span class="vip-profile-meta-label">Váha</span>
            <span class="vip-profile-meta-value">
                {{ ($profile->weight ?? '57') . ' kg' }}
            </span>
        </div>
        <div class="vip-profile-meta-row">
            <span class="vip-profile-meta-label">Výška</span>
            <span class="vip-profile-meta-value">
                {{ ($profile->height ?? '168') . ' cm' }}
            </span>
        </div>
        <div class="vip-profile-meta-row">
            <span class="vip-profile-meta-label">Prsa</span>
            <span class="vip-profile-meta-value">{{ $profile->bust_size ?? 'C' }}</span>
        </div>
        <div class="vip-profile-meta-row">
            <span class="vip-profile-meta-label">Jazyky</span>
            <span class="vip-profile-meta-value">{{ $profile->languages ?? 'Česky, Rusky, Anglicky' }}</span>
        </div>
    </div>

    <div class="vip-profile-flags">
        <div class="vip-profile-flag vip-profile-flag--incall">
            <span class="vip-profile-flag-status">
                <img src="{{ asset('images/icons/CircleCheck.svg') }}" alt="" aria-hidden="true">
            </span>
            <span>InCall</span>
        </div>
        <div class="vip-profile-flag vip-profile-flag--outcall">
            <span class="vip-profile-flag-status">
                <img src="{{ asset('images/icons/CircleX.svg') }}" alt="" aria-hidden="true">
            </span>
            <span>OutCall</span>
        </div>
    </div>

    @auth
        @if($profile->user_id && $profile->user_id !== auth()->id() && $messageRouteAvailable)
            <a href="{{ route('messages.show', $profile->user) }}" class="vip-profile-message">
                Poslat zprávu
                <img src="{{ asset('images/icons/message.svg') }}" alt="" aria-hidden="true" class="h-4 w-4 brightness-0 invert">
            </a>
        @else
            <span class="vip-profile-message" style="opacity:.6;pointer-events:none;">Poslat zprávu</span>
        @endif
    @else
        <a href="{{ $registerRouteAvailable ? route('register') : route('profiles.index') }}" class="vip-profile-message">
            Poslat zprávu
            <img src="{{ asset('images/icons/message.svg') }}" alt="" aria-hidden="true" class="h-4 w-4 brightness-0 invert">
        </a>
    @endauth

    <div class="vip-profile-contacts">
        <a class="vip-profile-contact-circle vip-profile-contact-circle--whatsapp" href="https://wa.me/420737155457" target="_blank" rel="noreferrer" title="WhatsApp">
            <img src="{{ asset('images/icons/whatsapp.svg') }}" alt="whatsapp" style="width: 20px; height: 20px;">
        </a>
        <a class="vip-profile-contact-circle vip-profile-contact-circle--telegram" href="https://t.me/alexandraprofil" target="_blank" rel="noreferrer" title="Telegram">
            <img src="{{ asset('images/icons/telegram.svg') }}" alt="telegram" style="width: 20px; height: 20px;">
        </a>
        <a href="tel:+420737155457" class="vip-profile-phone">
            <span>+420 737 155 457</span>
        </a>
    </div>
</aside>