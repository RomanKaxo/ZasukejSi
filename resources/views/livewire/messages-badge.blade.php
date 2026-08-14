{{--
    Header link to the inbox with a live unread count.

    Two shapes, both matching the markup the navbar used for its hardcoded
    "654": a 60x60 bordered button on desktop, and a row-aligned pill inside the
    mobile menu.
--}}
<div wire:poll.30s>
    @if($inline)
        @if($this->unreadCount > 0)
            <span class="absolute flex items-center justify-center"
                  style="right:16px;top:50%;transform:translateY(-50%);width:30px;height:30px;border-radius:999px;background:#00B80F;font-family:'Poppins',sans-serif;font-weight:700;font-size:11px;color:#FFFFFF;">
                {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
            </span>
        @endif
    @else
        <a href="{{ route('messages.index') }}"
           class="w-[60px] h-[60px] border border-[#DD3888] rounded-[8px] flex items-center justify-center relative"
           title="{{ __('front.account.member.messages') }}">
            <img src="{{ asset('images/icons/mail.svg') }}" class="w-[26px] h-[26px]" alt="{{ __('front.account.member.messages') }}">

            @if($this->unreadCount > 0)
                <span class="absolute -top-1 -right-1 w-[26px] h-[26px] bg-[#00B80F] rounded-full flex items-center justify-center font-bold text-[10px] text-white"
                      style="font-family: 'Poppins', sans-serif;">
                    {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                </span>
            @endif
        </a>
    @endif
</div>
