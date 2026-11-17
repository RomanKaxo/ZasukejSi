# Backendové optimalizace – 17. 11. 2026

Změny jsou lokální na větvi `opt/backend-perf`. Bez commitu, pushnutí a nasazení. Blade ani CSS se neměnily. Sdílené `vendor` a `node_modules` zůstaly nedotčené. Skutečný stack podle composer.json je Laravel 12 / Filament 4.

## Měření skutečných stránek

PHP 8.4 z `~/.local/opt/php-stanek/bin`, bez ext-intl; místní seedovaná SQLite `database/database.sqlite`, databázová cache a session. Server `php artisan serve --port=8041`. Jednovláknový vývojový server, bez souběžného benchmarku. Pro každou stránku jeden zahřívací požadavek a následně 5 požadavků se stejnou session; tabulka uvádí mediány. Homepage a detail jako host, účet po přihlášení lokálního seedovaného účtu woman@example.com. Všechny odpovědi HTTP 200. Locale cs.

Dočasný globální middleware s `DB::listen` měřil SQL od začátku middleware až po návrat odpovědi včetně zápisu session. HTTP čas měřil klient přes urllib a zahrnuje také start aplikace. Middleware i úpravy bootstrap/app.php jsou odstraněné. Hodnoty SQL nezahrnují bootstrap před vstupem do middleware; úplné síťové časy jsou ve sloupci HTTP.

| Stránka | SQL před → po | HTTP ms před → po | Aplikace ms před → po |
|---|---:|---:|---:|
| `/` | 131 → 31 | 323,6 → 329,8 | 132,1 → 124,8 |
| `/profiles/1` | 182 → 34 | 382,1 → 338,0 | 200,4 → 165,6 |
| `/account` | 35 → 31 | 238,4 → 231,6 | 56,5 → 58,7 |
| `/countries` (navíc) | 131 → 31 | 319,9 → 277,4 | 139,7 → 113,8 |

Medián součtu časů SQL z DB::listen: homepage 12,66 → 8,24 ms; detail 16,80 → 8,13 ms; účet 4,73 → 5,00 ms; země 13,84 → 7,79 ms. Jednotlivé query eventy ani součet SQL času nezahrnují hydrataci a renderování.

SQL pokles je opakovatelný. HTTP homepage se o 6,2 ms zhoršil; čas účtu uvnitř aplikace o 2,2 ms. Z pěti požadavků na sdíleném vývojovém počítači nelze pro tyto stránky tvrdit spolehlivé zrychlení celkové odezvy. Detail má v tomto běhu o 11,5 % nižší HTTP medián. Žádný údaj není produkční benchmark.

Na homepage původně 50 samostatných VIP dotazů a 25 count + 25 avg dotazů na hodnocení. Na detailu 98 VIP dotazů a 49 count dotazů přes detail a doporučovací slidery. Agregace jsou nyní součástí hlavního SELECTu, počet klientských SQL dotazů neroste s kartami. Korelované poddotazy nadále mají databázovou cenu; nejsou to výpočty zdarma.

Sdílený Composer autoload ve vendor směřoval App/Tests/Database na hlavní checkout. Měření i testy proto použily `/tmp/zsi-autoload.php`: předsazený autoloader na tento worktree a explicitní APP_BASE_PATH. Baseline App kód pocházel z HEAD v izolované kopii `/tmp/zsi-baseline/app`, bez přepisování pracovních změn. Před závěrečným baseline měřením byla nová migrace vrácena a pro měření po opět aplikována. Tyto pomocné soubory nejsou součástí aplikace.

## Změny

| Soubor:řádek | Důvod |
|---|---|
| `app/Livewire/ProfileList.php:231` | VIP exists, počet a průměr hodnocení načíst vždy; odstranit duplicitní agregace z větví filtrů. |
| `app/Livewire/CountryProfiles.php:286` | Stejné agregace pro výpis zemí. |
| `app/Livewire/ProfileSlider.php:105` | Totéž pro doporučení; zachovaný měsíční průměr pro měsíční řazení. |
| `app/Http/Controllers/ProfileController.php:97` | Agregace pro detail bez samostatných rating/VIP dotazů. |
| `app/Http/Controllers/Auth/AccountController.php:23` | loadMissing, agregace hodnocení, zpětná vazba na již načteného vlastníka. |
| `app/Livewire/ProfileForm.php:172` | Použít přihlášený model a načtený profil místo druhého SELECTu users a profiles. |
| `app/Models/Profile.php:600` | Respektovat načtenou activeSubscription i při opakovaném VIP dotazu. |
| `app/Models/Profile.php:957` | Cache seřazených obrázků v rámci instance. Klíč sleduje objekty médií, kolekci, pořadí a custom_properties; změna hlavní fotky, kolekce nebo reload vztahu přepočítá pořadí. Vrací klon kolekce, aby ji volající mohl měnit nezávisle. |
| `app/Models/Profile.php:1030` | getTotalRatings používá ratings_count včetně nulové hodnoty. |
| `app/Models/Profile.php:1141` | Po zápisu hodnocení zahodit agregované hodnoty na stejné instanci. |
| `app/Http/Middleware/SetLocale.php:32` | Session put jen při změně locale. Databázový session handler stále zapisuje session při ukončení požadavku; neuvádím úsporu fyzických SQL zápisů. |
| `database/migrations/2026_11_17_000001_add_public_profile_filter_indexes.php:14` | Čtyři složené indexy pro veřejnost + soft delete + datum / zemi / věk / actress. Existující indexy zůstávají zachované. |
| `tests/Feature/BackendPerformanceTest.php:56` | Nové regresní testy; podrobnosti níže. |

Memoizace obrázků: izolovaný CPU mikrobenchmark nad skutečnými 4 fotkami profilu 1, 5 × 10 000 volání na přednačteném modelu. Medián 507,6 → 38,7 ms, přibližně 13×. Nejde o čas stránky. Stejně jako dříve vyžadují změny médií provedené jiným modelem/procesem reload Eloquent vztahu; cache není mezipožadavková.

## Indexy

Izolovaná in-memory SQLite fixture se 100 000 syntetickými profily a původními relevantními indexy. Pět dávek po 20 identických SELECTech; medián na dotaz:

| Dotaz | Před ms | Po ms |
|---|---:|---:|
| Veřejné profily řazené datem a id | 9,134 | 0,006 |
| Veřejné profily podle země, datum a id | 10,295 | 0,006 |
| Počet profilů ve věkovém rozmezí | 6,555 | 0,289 |
| Actress filtr řazený datem a id | 6,813 | 0,005 |

Jde o SELECT id / COUNT, tedy příznivý případ pro covering index, nikoli simulaci celé Livewire stránky ani MySQL. EXPLAIN po úpravě používá nové indexy; u řazených dotazů zmizela dočasná B-tree pro třídění. Výchozí homepage navíc řadí JSON is_showcase: pro tento první klíč nový index třídění neodstraní. Čtyři indexy zvětšují úložiště a cenu zápisů, která nebyla na produkčním objemu ověřena.

SQLite up/down/up prošlo v lokální DB i regresním testu. MySQL grammar test ověřuje všechny čtyři ADD INDEX a DROP INDEX příkazy. MySQL server ani klient zde nejsou; skutečné vykonání migrace na MySQL není ověřené.

## Testy a vizuální ověření

10 nových testovacích případů: neměnný počet dotazů při 1 → 9 kartách ve třech komponentách; změny médií a nezávislé kolekce; invalidace rating agregací; načtená VIP relace bez SQL; locale beze změny i se změnou; SQLite migrace/rollback; kompilace MySQL migrace.

Na původním App kódu nové testy: 6 selhání, 4 průchody. Po úpravě: 10 průchodů, 41 assertions. Šest selhání tvoří tři testy růstu dotazů, invalidace průměru, načtený VIP vztah a vynechání session put.

Celá sada po úpravě, příkaz `php artisan test --bootstrap=/tmp/zsi-autoload.php` (PHP 8.4, lokální bootstrap nastavuje APP_BASE_PATH a CLI memory_limit=1G):

```text
Tests:    82 failed, 851 passed (2468 assertions)
Duration: 152.21s
```

Exit code 2. První pokusy s výchozími 128 MB skončily vyčerpáním paměti. Nejde o čistý průchod celé sady. Z aktuálních 933 případů prošlo 851, včetně všech 10 nových.

Srovnávací celá sada nad původním App kódem z HEAD, vynechané pouze nové BackendPerformanceTest případy:

```text
Tests:    82 failed, 841 passed (2426 assertions)
Duration: 146.13s
```

Exit code 2. Množina názvů selhávajících případů včetně jejich četností je shodná: žádná nová selhání ani vyřešená původní selhání. Ze selhání 60 obsahuje chybu chybějícího ext-intl ve Filamentu, dalších 22 jsou stávající kontroly obsahu, nastavení, překladů a importu. Neopravuju je v rámci tohoto výkonnostního zásahu. Kompletní oba výstupy jsou uvedené mezi podklady.

Screenshoty plných stránek při šířce 1280 px jsou v `/tmp/zsi-perf/*-{before,after}.png`. Účet 1280 × 4925: přesně 0 odlišných pixelů. Homepage i detail mají stejné rozměry a zachované údaje karet; úplná pixelová shoda neprošla kvůli dynamickému pozadí / sliderům a rozdílům při zachycení fixních prvků. Lokální kopie také nemá dostupné všechny soubory seedovaných fotek. U těchto dvou stránek nelze tvrdit úplnou pixelovou shodu. Žádný Blade ani CSS soubor se nezměnil.

## Záměrně neprovedené změny

- `resources/views/layouts/account.blade.php:26`: nahradit `services()->count()` čtením přednačteného services_count nebo exists. Změna šablony je podle zadání pouze doporučená; současný jeden dotaz zůstává.
- Mobilní a desktopové čítače zpráv/notifikací opakují dotazy; sjednocení vyžaduje navazující práci s jejich životním cyklem a invalidací. Šablony zůstaly nedotčené.
- Nepřidal jsem sdílenou HTTP cache veřejných HTML stránek: obsah závisí na locale, session, oprávnění k hodnocení a autentizaci, zapisují se imprese. Stávající privátní/no-store hlavičky médií respektují soukromí. Bez prokázaného přínosu se cache hlavičky nemění.
- Media již mají `(model_type, model_id)` index. Další index s collection_name neměl na reprezentativní místní kolekci prokázaný přínos, proto se nepřidává.
- SearchProfiles, allSegments a online stav se dále nepřepisovaly: dosavadní memoizace a eager loading jsou zachované; měření ukázalo především VIP a rating N+1. Nenačítám méně sloupců médií bez ověření všech potřeb MediaLibrary a generování URL.

## Podklady

- Pět jednotlivých běhů: `/tmp/zsi-before.json`, `/tmp/zsi-after.json`.
- SQL a časy bez bindings: `/tmp/zsi-queries.jsonl`.
- Klientský benchmark: `/tmp/zsi-bench.py`; middleware: `/tmp/zsi-probe.php` (od aplikace odpojený).
- Mikrobenchmark indexů a EXPLAIN: `/tmp/zsi-index-bench.py`, `/tmp/zsi-index-results.json`.
- Mikrobenchmark obrázků: `/tmp/zsi-images-bench.php`.
- Testy: `/tmp/zsi-perf/full-tests.txt`, `/tmp/zsi-perf/targeted-tests.txt`, `/tmp/zsi-perf/baseline-tests.txt`.
- Celá původní sada: `/tmp/zsi-perf/full-baseline-tests.txt`; porovnání všech selhání: `/tmp/zsi-perf/failure-comparison.json`.
- Pixelové porovnání: `/tmp/zsi-perf/visual-comparison.json`.

Server na 8041 je po ověření zastavený. Nová migrace zůstává aplikovaná pouze na místní SQLite kopii. Měřicí požadavky zapsaly běžné lokální imprese a návštěvy.
