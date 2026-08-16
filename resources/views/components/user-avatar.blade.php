@props(['user' => null, 'size' => 48])

@php
    // The photo if there is one, otherwise the initial. The messages screens
    // drew a coloured circle with a letter even for people who have a profile
    // photo, which made a list of conversations hard to scan.
    $photo = $user?->profile?->getFirstImageThumbUrl();
    $label = trim((string) ($user?->name ?? ''));
    $initial = $label !== '' ? mb_strtoupper(mb_substr($label, 0, 1)) : '?';
    $px = (int) $size;
@endphp

@if($photo)
    <img src="{{ $photo }}" alt="{{ $label }}"
         style="width:{{ $px }}px;height:{{ $px }}px;border-radius:999px;object-fit:cover;flex-shrink:0;" />
@else
    <span aria-hidden="true"
          style="width:{{ $px }}px;height:{{ $px }}px;border-radius:999px;background:#5C2D62;color:#FFFFFF;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;font-family:'Poppins',sans-serif;font-weight:700;font-size:{{ max(12, (int) round($px * 0.4)) }}px;">
        {{ $initial }}
    </span>
@endif
