<div
    {{ $attributes->merge([
        'class' => 'mx-auto flex w-full max-w-[1136px] items-center justify-center rounded-[24px] px-6 py-10 text-center md:h-[316px]'
    ]) }}
    style="background: linear-gradient(90deg, #FFF1F8 0%, #FFFFFF 50%, #FFF1F8 100%);"
>
    <div class="flex flex-col items-center justify-center">
        <x-icons name="HeartCrack" :preserveColors="true" class="mb-6 h-12 w-12" />

        <h3 class="mb-3 text-[36px] font-bold leading-none text-[#DD3888]" style="font-family: 'Poppins', sans-serif;">
            {{ __('front.profiles.list.empty_title') }}
        </h3>

        <p class="text-[14px] font-normal leading-[1.45] text-[#505050]" style="font-family: 'Poppins', sans-serif;">
            {{ __('front.profiles.list.empty_line_1') }}<br>
            {{ __('front.profiles.list.empty_line_2_prefix') }}
            <button type="button" wire:click.prevent="resetFilters" class="text-[#DD3888] underline underline-offset-2">
                {{ __('front.profiles.list.empty_link') }}
            </button>
        </p>
    </div>
</div>