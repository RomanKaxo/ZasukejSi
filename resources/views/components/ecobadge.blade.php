{{-- Desktop (lg): Figma home.jpg – a 35 px #E6FEE8 strip across the card
     column, 16 px leaf, 11 px type: bold green claim, en dash, grey line.
     Wording per viewport as the frames write it: the 1920 frame's line
     (profiles.list.eco_friendly*) on desktop, the phone frame's
     (footer.ecological / verification) below lg. --}}
<div {{ $attributes->merge(['class' => 'inline-flex w-full max-w-[309px] items-center gap-3 rounded-[12px] bg-[#E6FEE8] px-[18px] py-[14px] lg:max-w-[1136px] lg:h-[35px] lg:py-0 lg:justify-center lg:gap-[7px]']) }}>
    <img src="{{ asset('images/icons/eco.svg') }}" alt="" width="36" height="36" aria-hidden="true" class="h-[36px] w-[36px] shrink-0 md:h-[42px] md:w-[42px] lg:h-[16px] lg:w-[16px]" />

    <div class="flex min-w-0 flex-col gap-1 text-left lg:hidden">
        <span class="block text-[11px] leading-[1.1] md:text-[15px]" style="font-family: 'Poppins', sans-serif; font-weight: 800; color: #00B80F;">
            {{ __('front.footer.ecological') }}
        </span>
        <span class="block text-[11px] leading-[1.25] md:text-[12.8px]" style="font-family: 'Poppins', sans-serif; font-weight: 500; color: #505050;">
            {{ __('front.footer.verification') }}
        </span>
    </div>

    <div class="hidden min-w-0 lg:flex lg:flex-row lg:items-center lg:gap-[6px]">
        <span class="block text-[11px]" style="font-family: 'Poppins', sans-serif; font-weight: 800; color: #00B80F;">
            {{ __('front.profiles.list.eco_friendly') }}
        </span>
        <span class="block truncate text-[11px]" style="font-family: 'Poppins', sans-serif; font-weight: 400; color: #505050;">
            {{ __('front.profiles.list.eco_friendly_desc') }}
        </span>
    </div>
</div>
