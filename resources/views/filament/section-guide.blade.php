@php
    /**
     * Legenda k sekci administrace.
     *
     * Vlastní třídy a vlastní CSS (viz AdminPanelProvider): panel nemá
     * zkompilovaný theme, takže Tailwind utility napsané tady by v jeho CSS
     * nebyly a blok by se rozsypal.
     *
     * Sbalená se pamatuje zvlášť pro každou sekci — po druhé návštěvě už ji
     * člověk nepotřebuje.
     */
    $guide = \App\Support\AdminGuides::for(request()->path());
    $key = \App\Support\AdminGuides::key(request()->path());
    $panelPath = trim(config('filament.path', 'admin'), '/');
@endphp

@if($guide)
    <div class="zs-guide"
         x-data="{
            open: true,
            init() {
                try { this.open = localStorage.getItem('guide:{{ $key }}') !== 'closed'; } catch (e) {}
            },
            toggle() {
                this.open = !this.open;
                try { localStorage.setItem('guide:{{ $key }}', this.open ? 'open' : 'closed'); } catch (e) {}
            },
         }">
        <button type="button" class="zs-guide__head" @click="toggle()" :aria-expanded="open.toString()">
            <span class="zs-guide__mark" aria-hidden="true">i</span>
            <span class="zs-guide__title">K čemu je tahle sekce</span>
            <span class="zs-guide__chevron" :class="open ? 'is-open' : ''" aria-hidden="true">⌄</span>
        </button>

        <div class="zs-guide__body" x-show="open" x-cloak>
            <p class="zs-guide__intro">{{ $guide['intro'] }}</p>

            @if(!empty($guide['can']))
                <p class="zs-guide__label">Co tu jde</p>
                <ul class="zs-guide__list">
                    @foreach($guide['can'] as $line)
                        <li><span class="zs-guide__yes">✓</span>{{ $line }}</li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($guide['cannot']))
                <p class="zs-guide__label">Co tu nejde</p>
                <ul class="zs-guide__list">
                    @foreach($guide['cannot'] as $line)
                        <li><span class="zs-guide__no">✕</span>{{ $line }}</li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($guide['links']))
                <p class="zs-guide__label">Navazuje na</p>
                <div class="zs-guide__links">
                    @foreach($guide['links'] as $label => $slug)
                        <a href="{{ url($panelPath . '/' . $slug) }}">{{ $label }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
