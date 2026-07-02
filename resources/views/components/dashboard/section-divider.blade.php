@props(['noDesktopMargin' => false])

<div {{ $attributes->merge(['class' => 'w-[843px] max-[426px]:w-[313px] h-[1px] bg-[#E6E6E6] max-[426px]:mt-6 md:mt-2 max-[426px]:mb-6' . ($noDesktopMargin ? '' : ' md:mt-12')]) }}></div>