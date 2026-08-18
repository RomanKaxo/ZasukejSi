{{--
    Dílna scraperu.

    Vlastní třídy a vlastní CSS: panel nemá zkompilovaný theme, takže Tailwind
    utility napsané tady by v jeho CSS nebyly a výpis by se rozsypal.
--}}
<x-filament-panels::page>
    <style>
        .zs-wb { display: flex; flex-direction: column; gap: 1rem; }
        .zs-wb__actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 1.25rem; }
        .zs-wb__card {
            border: 1px solid rgba(148, 163, 184, .3);
            border-radius: .75rem;
            padding: 1rem 1.25rem;
            background: rgba(148, 163, 184, .06);
        }
        .zs-wb__card + .zs-wb__card { margin-top: 1rem; }
        .zs-wb__title { font-weight: 600; font-size: .95rem; margin: 0 0 .5rem; }
        .zs-wb__note { font-size: .85rem; line-height: 1.5; margin: .35rem 0; opacity: .85; }
        .zs-wb__table { width: 100%; border-collapse: collapse; font-size: .85rem; }
        .zs-wb__table th, .zs-wb__table td {
            text-align: left;
            vertical-align: top;
            padding: .5rem .6rem;
            border-bottom: 1px solid rgba(148, 163, 184, .25);
        }
        .zs-wb__table th { font-weight: 600; white-space: nowrap; }
        .zs-wb__scroll { overflow-x: auto; }
        .zs-wb__code {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .8rem;
            word-break: break-all;
        }
        .zs-wb__empty { opacity: .55; font-style: italic; }
        .zs-wb__bad { color: #dc2626; }
        .zs-wb__keys { display: flex; flex-wrap: wrap; gap: .35rem; margin: 0; padding: 0; list-style: none; }
        .zs-wb__keys li {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .75rem;
            border: 1px solid rgba(148, 163, 184, .4);
            border-radius: .35rem;
            padding: .15rem .4rem;
        }
    </style>

    <div class="zs-wb">
        <form wire:submit.prevent>
            {{ $this->form }}

            <div class="zs-wb__actions">
                <x-filament::button type="button" wire:click="probe" wire:loading.attr="disabled">
                    Prozkoumat web
                </x-filament::button>

                <x-filament::button type="button" color="gray" wire:click="testSelectors" wire:loading.attr="disabled">
                    Vyzkoušet selektory
                </x-filament::button>
            </div>
        </form>

        @if($error)
            <div class="zs-wb__card">
                <p class="zs-wb__title zs-wb__bad">Stránku se nepodařilo načíst</p>
                <p class="zs-wb__note">{{ $error }}</p>
            </div>
        @endif

        @if($probe)
            <div>
                <div class="zs-wb__card">
                    <p class="zs-wb__title">Zkoumaná stránka</p>
                    <p class="zs-wb__note zs-wb__code">{{ $probe['url'] }}</p>

                    <p class="zs-wb__note">
                        Deklarované kódování: <strong>{{ $probe['encoding']['declared'] ?? '—' }}</strong>
                        @unless($probe['encoding']['valid_utf8'] ?? true)
                            — stránka není platné UTF-8, převádí se automaticky.
                        @endunless
                    </p>
                </div>

                <div class="zs-wb__card">
                    <p class="zs-wb__title">robots.txt</p>

                    @if($probe['robots']['read'] ?? false)
                        <p class="zs-wb__note">
                            Zákazů: {{ $probe['robots']['disallow'] }} ·
                            Crawl-delay: {{ $probe['robots']['crawl_delay'] ?? 'neuveden' }} ·
                            použije se {{ $probe['robots']['effective_delay'] }} s
                        </p>

                        @if(!empty($probe['robots']['sitemaps']))
                            <p class="zs-wb__note">Ohlášené sitemapy:</p>
                            <ul class="zs-wb__keys">
                                @foreach($probe['robots']['sitemaps'] as $sitemap)
                                    <li>{{ $sitemap }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @else
                        <p class="zs-wb__note zs-wb__empty">Nepodařilo se načíst: {{ $probe['robots']['error'] ?? 'neznámý důvod' }}</p>
                    @endif
                </div>

                <div class="zs-wb__card">
                    <p class="zs-wb__title">Sitemapa</p>

                    @if($probe['sitemap']['found'] ?? false)
                        <p class="zs-wb__note">
                            Nalezena na <span class="zs-wb__code">{{ $probe['sitemap']['url'] }}</span>,
                            adres: {{ $probe['sitemap']['count'] }}.
                        </p>
                        <ul class="zs-wb__keys">
                            @foreach($probe['sitemap']['sample'] as $sample)
                                <li>{{ $sample }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="zs-wb__note zs-wb__empty">Web sitemapu nenabízí — adresy se budou muset hledat ve výpisu.</p>
                    @endif
                </div>

                <div class="zs-wb__card">
                    <p class="zs-wb__title">Skupiny odkazů, které vypadají jako profily</p>

                    @if(empty($probe['links']))
                        <p class="zs-wb__note zs-wb__empty">Žádná skupina podobných odkazů.</p>
                    @else
                        <div class="zs-wb__scroll">
                            <table class="zs-wb__table">
                                <thead>
                                    <tr>
                                        <th>Tvar adresy</th>
                                        <th>Odkazů</th>
                                        <th>Selektor</th>
                                        <th>Filtr adres</th>
                                        <th>Příklad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($probe['links'] as $candidate)
                                        <tr>
                                            <td class="zs-wb__code">{{ $candidate['shape'] }}</td>
                                            <td>{{ $candidate['count'] }}</td>
                                            <td class="zs-wb__code">{{ $candidate['detail_link_selector'] }}</td>
                                            <td class="zs-wb__code">{{ $candidate['detail_url_pattern'] }}</td>
                                            <td class="zs-wb__code">{{ $candidate['sample'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="zs-wb__note">
                            Selektor patří do nastavení zdroje jako <span class="zs-wb__code">detail_link_selector</span>,
                            filtr jako <span class="zs-wb__code">detail_url_pattern</span>.
                        </p>
                    @endif
                </div>

                @if($probe['client_rendered'] ?? false)
                    <div class="zs-wb__card">
                        <p class="zs-wb__title">Stránka se skládá až v prohlížeči</p>
                        <p class="zs-wb__note">
                            Text, který by návštěvník viděl, v HTML skoro není — obsah dosazuje JavaScript.
                            Scraper ho nespouští, takže obyčejné selektory tu nenajdou nic.
                        </p>

                        @if(empty($probe['embedded']))
                            <p class="zs-wb__note">
                                A data v sobě stránka nemá. Zbývá stahovat přes externí renderer, nebo tenhle web vynechat.
                            </p>
                        @else
                            <p class="zs-wb__note">
                                Data ale veze s sebou — jinak by je musela stahovat dvakrát. Tyhle klíče jdou použít
                                jako selektor a jsou to hodnoty z vlastní datové struktury webu, takže přežijí i redesign.
                            </p>
                            <ul class="zs-wb__keys">
                                @foreach(array_slice($probe['embedded'], 0, 120) as $key)
                                    <li>{{ $key }}</li>
                                @endforeach
                            </ul>
                            @if(count($probe['embedded']) > 120)
                                <p class="zs-wb__note zs-wb__empty">… a dalších {{ count($probe['embedded']) - 120 }}.</p>
                            @endif
                        @endif
                    </div>
                @endif

                <div class="zs-wb__card">
                    <p class="zs-wb__title">Co web zveřejňuje sám o sobě</p>

                    @if(empty($probe['structured']))
                        <p class="zs-wb__note zs-wb__empty">Nic — žádné JSON-LD ani použitelné meta značky.</p>
                    @else
                        <p class="zs-wb__note">
                            Tyhle klíče jdou zapsat rovnou jako selektor u mapování pole. Nezávisí na vzhledu stránky,
                            takže přežijí i redesign.
                        </p>
                        <ul class="zs-wb__keys">
                            @foreach($probe['structured'] as $key)
                                <li>{{ $key }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if(!empty($probe['notes']))
                    <div class="zs-wb__card">
                        <p class="zs-wb__title">Poznámky</p>
                        @foreach($probe['notes'] as $note)
                            <p class="zs-wb__note">{{ $note }}</p>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if($selectors !== null)
            <div class="zs-wb__card">
                <p class="zs-wb__title">Co selektory na téhle stránce vrátily</p>

                @if($lastSourceLabel)
                    <p class="zs-wb__note">Zdroj HTML: {{ $lastSourceLabel }}</p>
                @endif

                @if($selectors === [])
                    <p class="zs-wb__note zs-wb__empty">Zdroj nemá žádná mapování polí.</p>
                @else
                    <div class="zs-wb__scroll">
                        <table class="zs-wb__table">
                            <thead>
                                <tr>
                                    <th>Pole</th>
                                    <th>Selektor</th>
                                    <th>Nalezeno</th>
                                    <th>Po transformacích</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectors as $row)
                                    <tr>
                                        <td>
                                            {{ $row['field'] }}
                                            @if($row['required'])
                                                <span class="zs-wb__note">povinné</span>
                                            @endif
                                        </td>
                                        <td class="zs-wb__code">{{ $row['selector'] }}</td>
                                        <td>
                                            @if($row['error'])
                                                <span class="zs-wb__bad">{{ $row['error'] }}</span>
                                            @elseif($row['raw'] === null)
                                                <span class="zs-wb__empty">nic</span>
                                            @else
                                                {{ $row['raw'] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($row['value'] === null)
                                                <span class="zs-wb__empty">nic</span>
                                            @else
                                                {{ $row['value'] }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="zs-wb__note">
                        Sloupce vedle sebe kvůli tomu, co se jinak nedá rozlišit: selektor, který nenašel nic,
                        vypadá stejně jako selektor, který našel správně a hodnotu pak zahodila transformace.
                    </p>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
