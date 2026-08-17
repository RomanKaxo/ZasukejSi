@php
    /**
     * Legenda k sekci administrace.
     *
     * Sbalená, protože po druhé návštěvě už ji člověk nepotřebuje; stav si
     * pamatuje prohlížeč zvlášť pro každou sekci.
     */
    $guide = \App\Support\AdminGuides::for(request()->path());
    $key = \App\Support\AdminGuides::key(request()->path());
@endphp

@if($guide)
    <div x-data="{
            open: false,
            init() {
                try { this.open = localStorage.getItem('guide:{{ $key }}') !== 'closed'; } catch (e) { this.open = true; }
            },
            toggle() {
                this.open = !this.open;
                try { localStorage.setItem('guide:{{ $key }}', this.open ? 'open' : 'closed'); } catch (e) {}
            },
         }"
         class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
         style="margin-bottom:1.5rem;">

        <button type="button" @click="toggle()"
                class="flex w-full items-center gap-3 px-4 py-3 text-left"
                :aria-expanded="open.toString()">
            <svg class="h-5 w-5 flex-none" style="color:#DD3888;" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25h1.5v5.25M12 7.5h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>

            <span class="text-sm font-semibold text-gray-950 dark:text-white">K čemu je tahle sekce</span>

            <svg class="ml-auto h-4 w-4 flex-none text-gray-400 transition-transform"
                 :class="open ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
            </svg>
        </button>

        <div x-show="open" x-cloak class="px-4 pb-4" style="padding-left:3rem;">
            <p class="text-sm text-gray-600 dark:text-gray-300" style="max-width:62ch;">{{ $guide['intro'] }}</p>

            @if(!empty($guide['can']))
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Co tu jde</p>
                <ul class="mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-300" style="max-width:62ch;">
                    @foreach($guide['can'] as $line)
                        <li class="flex gap-2">
                            <span style="color:#1B6E3C;">✓</span>
                            <span>{{ $line }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($guide['cannot']))
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Co tu nejde</p>
                <ul class="mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-300" style="max-width:62ch;">
                    @foreach($guide['cannot'] as $line)
                        <li class="flex gap-2">
                            <span style="color:#B3261E;">✕</span>
                            <span>{{ $line }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($guide['links']))
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Navazuje na</p>
                <div class="mt-1 flex flex-wrap gap-2">
                    @foreach($guide['links'] as $label => $slug)
                        <a href="{{ url(trim(config('filament.path', 'admin'), '/') . '/' . $slug) }}"
                           class="rounded-lg px-2 py-1 text-sm"
                           style="background:#FBEAF2;color:#C42A76;text-decoration:none;">{{ $label }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
