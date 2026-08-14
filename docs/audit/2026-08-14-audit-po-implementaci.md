# ZasukejSi — audit po implementaci

**Datum:** 14. 8. 2026
**Rozsah:** dokončení fází F0–F10 podle schváleného plánu — propojení frontendu s databází a administrací.
**Stav testů:** `103 prošlo (274 assertions)`. Na začátku: `47 prošlo, 2 selhaly`.
**Rozsah změn:** 42 upravených souborů (+1 238 / −508 řádků), 26 nových souborů.

---

## 1. Jak to skončilo

| Ukazatel | Před | Po |
|---|---|---|
| Testy | 47 ✓ / 2 ✗ | **103 ✓ / 0 ✗** |
| Profily na homepage | 5 opakovaných, fabrikovaný total 150 | **25 skutečných, reálný total** |
| Země v seznamu | 24 položek (8 zemí 3×) | **14 unikátních, 0 duplicit** |
| Zvonek v hlavičce | statický `<div>`, badge `14` | **funkční dropdown, reálný počet** |
| Badge zpráv | `654` napevno | **reálný počet nepřečtených** |
| Ceník na detailu | vymyšlených 4 000–18 000 Kč | **jen skutečný, jinak prázdný stav** |
| Statistiky | pevná zářijová data | **z `profile_views`, s funkční navigací měsíců** |
| Odkazy v patičce | 6× `href="#"` | **6 skutečných stránek z CMS** |
| Novinky na homepage | 2 hardcoded karty | **skutečné blogové články** |
| Nástěnka administrace | prázdná | **4 provozní ukazatele** |
| Editace profilu v adminu | **padala TypeError** | **funguje** |

---

## 2. Co se během implementace našlo navíc

Tyto vady nebyly v původním auditu — vyplavaly až při práci na kódu a spuštění testů. Několik z nich bylo závažnějších než to, co plán řešil.

### N1 — Profil s vyplněnými parametry nešel v administraci vůbec otevřít (**P0, blokující**)

`profiles.content` používaly **dva různé editory**: provozní `App\Livewire\ProfileForm` (asociativní mapa `card_height_cm`, `weight_kg`, `languages`, `is_showcase`…) a Filament `BlocksInput` (seznam bloků `{type, data}`).

Filament `Builder::getItems()` iteruje surový stav a typuje položku jako `array`:

```
Argument #1 ($itemData) must be of type array, int given
```

Následek: **každý profil, který měl vyplněnou výšku, hodil při otevření v administraci výjimku** — a kdyby se uložit dal, přepsal by poskytovatelce všechny parametry.

**Řešení:** migrace `2026_08_14_000003_split_profile_content_blocks` dala blokovému editoru vlastní sloupec `content_blocks` a přesunula do něj data ve tvaru seznamu. `App\Support\ProfileContentState::merge()` navíc slučuje mapu tak, aby jeden editor nikdy nepřepsal klíče druhého.

Regresi hlídá `ProfileAdminContentIntegrityTest`.

### N2 — Příčina „168 cm" nebyl hardcode, ale nenačtený sloupec (**P1**)

`components/profile-card.blade.php` má `$cardContent['card_height_cm'] ?? 168`. Jenže `ProfileList` i `ProfileSlider` selektovaly explicitní seznam sloupců **bez `content`** → atribut byl vždy `null` → fallback se uplatnil i u profilů, které výšku vyplněnou měly.

Původní audit tvrdil, že jde o natvrdo zapsanou hodnotu v `livewire/profile-card.blade.php`. **To byla nepřesnost** — ten soubor je osiřelý a nikdy se nevykresluje (viz O2 níže). Živá karta je `components/profile-card.blade.php`.

### N3 — Nekonzistentní formát `viewed_date` rozděloval statistiky (**P1**)

`ProfileView::recordClick()` zapisuje přes `create()` → cast `date` serializuje na `2026-08-14 00:00:00`.
`ProfileView::recordImpressions()` zapisuje přes raw `insert()` → obchází casty → `2026-08-14`.

`getDailyStats()` seskupuje podle surového sloupce, takže **tentýž den padal do dvou různých košů**. Vyřešeno mutátorem `setViewedDateAttribute()` a normalizací klíčů při čtení.

### N4 — Livewire memoizuje `getXProperty()` (**P1**)

`ProfileStatistics::buildChart()` četl měsíc přes `$this->currentMonth`. Livewire tento accessor memoizuje na dobu requestu, takže po `previousMonth()` dostal **starou hodnotu** a graf se přepočítal na měsíc, který už zobrazoval. Odděleno na privátní `monthStart()`.

Stejná třída chyby: `recordImpressions()` volal `$this->profiles()` jako **metodu**, čímž obcházel cache `#[Computed]` a spouštěl celý dotaz podruhé při každém renderu. Opraveno na přístup přes vlastnost.

### N5 — Ovládání měsíců ve statistikách bylo mrtvé (**P1**)

Tlačítka `#stats-prev` / `#stats-next` neměla `wire:click` ani JS handler — čistá atrapa. Navíc inline skript grafu používal **Livewire 2 API** (`livewire:load`, `message.processed`), které se v Livewire 3 nikdy nespustí, a data měl zapečená přes `@json` uvnitř `wire:ignore` bloku. Graf se tedy po jakékoli změně nemohl překreslit ani teoreticky.

Vyřešeno: data jedou přes `data-chart` na kořenovém prvku komponenty (mimo `wire:ignore`), hooky přepsány na `livewire:initialized` + `morph.updated`.

### N6 — `PageSeeder` existoval, ale nikdy se nespouštěl (**P1**)

Kompletní seeder s 5 blogovými články, FAQ a 5 stránkami **nebyl zaregistrován v `DatabaseSeeder`**. Tabulka `pages` byla prázdná, a proto:
- navigace v hlavičce padala na hardcoded fallback,
- patička neměla co zobrazit (a tak měla `href="#"`),
- sekce novinek neměla články (a tak byla hardcoded).

Jedna neprovedená registrace stála za třemi „statickými" sekcemi.

### N7 — Míchané velikosti písmen v `country_code` (potvrzeno měřením)

`profiles.country_code` obsahoval `["AT","CZ","DE","PL","SK","cz","us"]`, `cities` vždy velká. Join na země tak na SQLite tiše ztrácel část profilů; na MySQL to zachraňovala jen collation. Normalizováno migrací + mutátorem.

---

## 3. Co je teď řízené z administrace

| Sekce webu | Kde se spravuje | Bylo |
|---|---|---|
| Navigace v hlavičce | Stránky → *Zobrazit v menu* | hardcoded fallback |
| Odkazy v patičce | Stránky → *Zobrazit v patičce* | 6× `href="#"` |
| Novinky na homepage | Blog příspěvky | 2 hardcoded karty |
| Seznam zemí + pořadí + viditelnost | **Nastavení → Země** (nové) | 3 hardcoded pole |
| Doporučené profily na homepage | Profily → *Doporučený profil* | skrytý JSON příznak |
| Výška, váha, prsa, národnost, jazyky | Profily (nová pole) | jen z profilu poskytovatelky |
| Segmenty, služby, města | beze změny | — |

**Nová administrační sekce `Nastavení → Země`** — drag&drop řazení, přepínač viditelnosti, volitelný vlastní název. Počty profilů jsou **read-only a vždy dopočítané z DB**, takže administrátor nemůže zveřejnit číslo, které výpis nepodloží.

---

## 4. Klíčová architektonická rozhodnutí

**Prázdné stavy.** Komponenta `<x-empty-value />` záměrně nenese vlastní `font-family`, `font-size` ani `font-weight` — vše dědí z rodiče. Dlaždice si proto zachová přesně stejné metriky, ať už hodnotu má, nebo ne. Ztlumení řeší `currentColor` + `opacity`, takže funguje na světlém i tmavém podkladu. Konvence: [`docs/conventions/empty-values.md`](../conventions/empty-values.md).

**Doporučené profily.** Původní mechanika brala 5 profilů, opakovala je do 6 stránek a paginátoru podstrčila `total = perPage * 6`. Nově je `is_showcase` **pouze řazení** — doporučené jdou první, ale seznam obsahuje všechny profily a total je pravdivý. Demo profily tak zůstaly (jak jste chtěl), jen se přestaly tvářit jako 150 různých žen.

**Počty zemí.** `CountryStatsService` používá **přesně stejnou podmínku jako výpis za odkazem** (schválený + veřejný + ověřený). Kdyby se rozešly, panel by sliboval číslo, které seznam nedodá. Země s nulou v seznamu zůstává — je to navigační prvek, ne skladová karta.

**Imprese.** Přesunuty z `ProfileController@index` (kde se počítaly nad úplně jinou množinou, než jaká se zobrazila) do `ProfileList::render()`, nad skutečně vykreslenou stránku. Vlastní profil poskytovatelky se nezapočítává.

---

## 5. Nové testy (56 přibylo)

| Sada | Co hlídá |
|---|---|
| `EmptyValueComponentTest` | prázdný stav nemění metriky, nenese typografii |
| `ProfileCardRealDataTest` | `content` dorazí do karty; nikdy se nezobrazí 168 ani cizí fotka |
| `CountryStatsServiceTest` | unikátnost, nula zůstává, shoda počtů s výpisem, normalizace kódů |
| `HomepageRealDataTest` | pravdivý total, žádné duplicity, imprese nad zobrazenou stránkou |
| `HeaderNotificationsTest` | per-user čtení globálních notifikací, reálné počty, autorizace |
| `ProfileDetailNoFabricatedDataTest` | žádné vymyšlené ceny, sekce zůstávají s prázdným stavem |
| `ProfileStatisticsRealDataTest` | data z `profile_views`, navigace měsíců, žádný cizí profil |
| `ProfileAdminContentIntegrityTest` | uložení v adminu nesmaže parametry profilu |

---

## 6. Otevřené body — vyžadují vaše rozhodnutí

| # | Věc | Proč to nechávám na vás |
|---|---|---|
| **O1** | `Profile::isOnline()` stále simuluje online stav u ~30 % profilů (deterministicky, `crc32`, 20min okno) | Je to **vědomé produktové rozhodnutí**, ne chyba — komentář v kódu to přiznává („so the site doesn't look empty"). Odporuje pravidlu „žádná vymyšlená data", ale odstranění změní vzhled webu. Navrhuji přepínač v administraci. |
| **O2** | `resources/views/livewire/profile-card.blade.php` — osiřelý soubor, nikde nereferencovaný, neexistuje k němu Livewire komponenta | Obsahuje natvrdo `168 cm` a `4,9/5`, ale **nikdy se nevykreslí**. Ponechán podle pravidla „nic z frontendu neodstraňovat". Doporučuji smazat. |
| **O3** | Škála hodnocení 100 % / 70 % / 30 % mapuje na 5 / 4 / 2 hvězdy | Hodnoty 3 a 1 jsou nedosažitelné, průměry v `ratings` jsou tím zkreslené. Oprava mění význam už uložených dat. |
| **O4** | Chart.js se načítá z `cdn.jsdelivr.net` | Externí závislost v produkci (dostupnost, GDPR). Projekt už bundluje přes Vite — doporučuji přesunout tam. |

---

## 7. Co zbývá před nasazením

**Nutné:**
- Zapnout `extension=gd` na produkčním serveru (už deklarováno v `composer.json`)
- Vyplnit `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` (už v `.env.example`)
- Spustit `queue:work` pod supervisorem — bez něj se nezpracují konverze obrázků ani fronty
- MySQL/MariaDB (SQLite je jen dev cesta)

**Doporučené:**
- `php artisan translations:audit` **prochází**, ale hlásí ~200 podezřelých hardcoded textů. `account/profile/edit.blade.php` je celý mimo překlady. Po dočištění zapnout `--strict-hardcoded` jako bránu v CI.
- Doplnit testy pro Stripe checkout a autentizaci (zatím nepokryté).
- Vizuální regrese: zkontrolovat 375 / 768 / 1280 px na homepage, `/countries`, detailu profilu a `/account/statistics`.

---

## 8. Ověřeno živě

```
200  /                 200  /?locale=en      200  /countries
200  /faq              200  /contact         200  /vip-premium
200  /privacy-policy   200  /safety-tips     200  /profiles/3
200  /profiles/21
```

Všechny odkazy v hlavičce i patičce vracejí 200. V administraci ověřeno: seznam zemí s reálnými počty, editace profilu s novými poli (výška 168 se načte ze skutečných dat).

**Stav dat:** 25 veřejných profilů · 14 zemí · 11 stránek (5 blog, 6 v patičce) · 227 zaznamenaných zobrazení.
