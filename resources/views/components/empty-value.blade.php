{{--
    Neutral placeholder for a value the profile has not filled in.

    The project rule is that no number, price or attribute may ever be invented:
    a value is either true or absent. The surrounding tile/section still renders
    (the layout must not shift), only the value itself is replaced by this
    component.

    It deliberately carries no font-family/size/weight of its own — it inherits
    everything from the element it sits in, so the box keeps exactly the same
    metrics as when a real value is present. Only the colour is muted, via
    currentColor + opacity, so it works on light and dark surfaces alike.

    Usage:
        {{ $profile->height ?? '' }}          ← never do this
        @if($height) {{ $height }} cm @else <x-empty-value /> @endif

    Variants:
        dash (default) — an em dash, for compact tiles (height, weight, price)
        text           — "neuvedeno" / "not specified", for wider blocks
--}}
@props([
    'variant' => 'dash',
])

@php
    $emptyLabel = $variant === 'text'
        ? __('front.common.not_specified')
        : __('front.common.dash');
@endphp

<span {{ $attributes->merge([
        'class' => 'empty-value',
        'style' => 'color:currentColor;opacity:0.45;',
        'aria-label' => __('front.common.not_specified'),
    ]) }}>{{ $emptyLabel }}</span>
