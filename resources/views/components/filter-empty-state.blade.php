{{-- home-21.jpg: 1136x315 panel in the card column, broken heart (ink
     38 px tall), 19 px to the 35 px title, 24 px to the two 14 px lines
     20 px apart; content centred. --}}
<div
    {{ $attributes->merge([
        'class' => 'mx-auto flex w-full max-w-[1136px] items-center justify-center rounded-[24px] px-6 py-10 md:pt-[34px] md:pb-[46px] text-center md:h-[315px] filter-empty-state'
    ]) }}
    style="background: linear-gradient(90deg, #FFF1F8 0%, #FFFFFF 50%, #FFF1F8 100%);"
>
    <div class="flex flex-col items-center justify-center">
        <x-icons name="HeartCrack" :preserveColors="true" class="mb-6 md:mb-[10px] h-12 w-12 md:h-[50px] md:w-[50px]" />

        <h3 class="mb-3 md:mb-[14px] text-[36px] md:text-[35px] font-bold leading-none text-[#DD3888]" style="font-family: 'Poppins', sans-serif;">
            {{ __('front.profiles.list.empty_title') }}
        </h3>

        <p class="text-[14px] font-normal leading-[1.45] md:leading-[20px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">
            {{ __('front.profiles.list.empty_line_1') }}<br>
            {{ __('front.profiles.list.empty_line_2_prefix') }}
            <button type="button" wire:click.prevent="resetFilters" class="text-[#DD3888] underline underline-offset-2">
                {{ __('front.profiles.list.empty_link') }}
            </button>
        </p>
    </div>
</div>
