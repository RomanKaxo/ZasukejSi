@extends('layouts.app')

@section('content')
<!-- Add top padding to account for fixed navbar -->
<div class="container mx-auto pt-24 md:pt-38 min-h-screen px-4 md:px-0">
    {{-- Membership status. Lives here rather than pasted into each member view
         — it used to be duplicated across five of them, each with a hardcoded
         date. Renders only when the member actually holds a membership. --}}
    <x-premium-banner />

    {{-- The sidebar has no way to know how tall the page's own heading (above
         the main divider) renders, so its top offset is calibrated by hand per
         page to line up with that divider. Recalibrate the matching value
         below whenever a heading's text/translation changes height. --}}
    @php
        $sidebarOffsetClass = match($sidebarOffset ?? null) {
            3 => 'md:mt-[3px]',      // ratings, favorites
            95 => 'md:mt-[95px]',    // girls-of-month, reported
            116 => 'md:mt-[116px]',  // archive (description wraps to 2 lines)
            default => ($wideContent ?? false) ? 'md:mt-[110px]' : '',
        };
    @endphp
    <div class="flex flex-col md:flex-row md:justify-center md:gap-[20px]">
        <!-- Sidebar -->
        <div class="{{ $sidebarOffsetClass }}">
            <x-member-sidebar :activeItem="$activeItem ?? 'dashboard'" />
        </div>

        <!-- Main Content -->
        <main class="flex-1 md:flex-none {{ ($wideContent ?? false) ? 'md:w-[903px]' : 'md:w-[843px]' }} pt-10 px-0 md:px-0 md:pt-0">

                @yield('member-content')

        </main>
    </div>
</div>
<x-reported-case-modal />
@endsection
