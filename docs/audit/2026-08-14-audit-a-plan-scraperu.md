# ZasukejSi — kompletní audit a plán dalšího vývoje

**Datum:** 14. 8. 2026
**Rozsah:** lokální spuštění, funkční audit celé aplikace, kontrola notifikací v hlavičce, inventář statického obsahu, návrh scraperu do administrace.
**Stav:** analýza dokončena, **do kódu nebylo zasaženo** (jediné vytvořené soubory jsou `.claude/launch.json` a tento dokument).

---

## 1. Lokální spuštění

### 1.1 Co bylo provedeno

| Krok | Příkaz | Výsledek |
|---|---|---|
| Klon | `git clone https://github.com/RomanKaxo/ZasukejSi` | OK (nutný `-c http.sslBackend=schannel`, chybí CA bundle v Git for Windows) |
| PHP závislosti | `composer install` | OK, 108 balíčků |
| JS závislosti | `npm install` | OK |
| Klíč | `php artisan key:generate` | OK |
| Migrace | `php artisan migrate` | OK, 45 migrací |
| Seed | `php artisan db:seed` | OK (48 059 měst, 25 veřejných profilů) |
| Build | `npm run build` | OK, 87 modulů |
| Server | `php artisan serve --port=8123` | běží, HTTP 200 |

**Přihlašovací údaje (seed):** `test@example.com` / `admin123` (super_admin), `woman@example.com` / `password`, `user@example.com` / `password`.
**Administrace:** `/admin` — funkční, panel Filament v4.0.7.

### 1.2 Odchylky od produkčního prostředí — nutno vyřešit

| # | Problém | Dopad | Řešení |
|---|---|---|---|
| E1 | **`.env.example` předepisuje MySQL**, na stroji není MySQL server. Spuštěno na SQLite. | Migrace prošly (jediná MySQL-only migrace `2026_07_31_171816` je korektně ošetřená přes `supportsEnumModification()`), ale `ENUM` sloupce nejsou vynucené. | Pro produkci MySQL/MariaDB. Pro lokální dev doplnit do README oficiálně podporovanou SQLite cestu. |
| E2 | **Chybí PHP rozšíření `gd`** (`C:\php\php.ini`, řádek 921 `;extension=gd`). Bez něj seeder padá na `Spatie\Image\Exceptions\CouldNotLoadImage`. | Nelze zpracovat žádnou fotku (media library konverze `thumb`, `medium`). | Zapnout `extension=gd` v `php.ini`. Obcházeno přes `PHP_INI_SCAN_DIR`. Doplnit do `composer.json` → `require.ext-gd`. |
| E3 | **`.env.example` neobsahuje Stripe klíče.** `config/services.php` čte `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`. | Předplatné je v čerstvé instalaci nefunkční a selže až za běhu při checkoutu. | Doplnit 3 klíče do `.env.example`. |
| E4 | Chybí `.nvmrc`-kompatibilní Node — `package.json` vyžaduje `>=22 <23`, běží 24.16.0. | Build proběhl, ale mimo deklarovaný rozsah. | Sjednotit `engines`. |

### 1.3 Stav testů

```
Tests: 2 failed, 47 passed (130 assertions)
```

**Oba pády — `Tests\Feature\ProfilePhysicalAttributesFlowTest`:**
```
PublicPropertyNotFoundException:
Unable to set component data. Public property [$languages] not found on component: [profile-form]
```

Jde o **skutečnou regresi, ne o vadný test** — viz nález **F7** níže.

**Deprecation (PHP 8.4):** `App\Models\ProfileView::getDailyStats()` a `getTotalStats()` — implicitně nullable parametr `$type`, nutné `?string`.

---

## 2. Architektura a inventář

### 2.1 Stack

Laravel 12 · PHP 8.2+ · Filament 4 (+ Shield, Language Switch, Media Library, Blocks Builder) · Livewire 3 · Alpine.js 3 · Tailwind 4 · Vite 5 · Swiper 12 · spatie/laravel-translatable · spatie/laravel-medialibrary · Stripe PHP 21 · PHPUnit 11.

### 2.2 Doménový model (17 modelů)

```
User ─┬─ 1:1 Profile ─┬─ n:m Service      (profile_service)
      │               ├─ n:m Segment      (profile_segment)
      │               ├─ 1:n Rating
      │               ├─ 1:n ProfileView  (click | impression)
      │               ├─ 1:n Subscription ── n:1 SubscriptionType
      │               │                  └─ 1:n SubscriptionLog
      │               ├─ 1:n Report          (nahlášení od přihlášeného)
      │               ├─ 1:n ProfileReport   (anonymní nahlášení)
      │               ├─ n:m User (favorites, profile_favorites)
      │               └─ Media (profile-images, profile-video)
      ├─ 1:n Message (from/to)
      └─ 1:n Notification ── 1:n NotificationUserState (per-user stav globálních)

City   (48 059 řádků, autocomplete + mapování kraje přes admin_name)
Page   (type = page | blog, translatable, blocks-builder content)
```

### 2.3 Veřejné routy (`routes/web.php`)

| Routa | Handler | Poznámka |
|---|---|---|
| `/` | `ProfileController@index` → `livewire:profile-list` | ⚠️ viz F1 |
| `/profiles/{id}` | `ProfileController@show` | ⚠️ viz F5 |
| `/countries` | closure → `livewire:country-profiles` | ⚠️ viz F2 |
| `/api/profiles` | `ProfileController@api` | throttle 60/1 |
| `/{slug}` | closure → `Page` | catch-all, dynamické stránky |
| auth: login/register/forgot/reset/verify | `Auth\*Controller` | funkční |
| `/notifications/*` | `NotificationController` | funkční backend, ⚠️ viz F3 |
| `/messages/*` | `MessageController` | funkční, throttle 20/1 |
| `/account/*` | `AccountController` + `SubscriptionCheckoutController` | `gender:female` + `profile.exists` |
| `/account/member/*` | `Auth\MemberController` | `gender:male` |
| `/stripe/webhook` | `SubscriptionCheckoutController@webhook` | mimo CSRF |

### 2.4 Livewire komponenty (16)

`ProfileList` · `SearchProfiles` · `CountryProfiles` · `ProfileSlider` · `ProfileForm` · `ProfileStatistics` · `ProfileRating` · `MemberRatings` · `PhotosManager` · `ServicesManager` · `FavoriteButton` · `ReportProfileModal` · `LoginModal` · `RegisterModal` · `ResetModal` · **`NotificationsDropdown` (mrtvý kód — nikde nepoužit)**

### 2.5 Administrace (Filament) — ověřeno živě

Navigace je **kompletní**, skupiny jsou jen sbalené:

- **Nástěnka** — pouze `AccountWidget` + `FilamentInfoWidget`, tj. **prázdná**
- Profily · Uživatelé · Ověření fotografií · Služby · Segmenty · Stránky · Blog příspěvky
- **Filament Shield** → Role
- **Předplatná** → Typy předplatného · Předplatná · Log předplatných
- **Moderace** → Nahlášení profilů · Anonymní nahlášení · Hodnocení · Soukromé zprávy
- **Nastavení** → Města · Notifikace
- **Statistiky** → Zobrazení profilů

**Scraper zatím neexistuje** — v `app/` není jediný výskyt `scrap`, `crawl`, `import`, `Http::get`, `DomCrawler`. Jde o greenfield.

---

## 3. Notifikace v hlavičce — detailní kontrola

Toto byl explicitní požadavek. Výsledek: **zvonek v hlavičce je nefunkční atrapa.**

### 3.1 Co je v hlavičce

`resources/views/components/navbar.blade.php:216–228`:

```blade
<!-- Notifications Button -->
<div class="relative w-[60px] h-[60px]">
    <div class="w-[60px] h-[60px] border border-[#DD3888] rounded-[8px] flex items-center justify-center">
        <img src="{{ asset('images/icons/bell.svg') }}" class="w-[26px] h-[26px]" alt="Bell">
    </div>
    <div class="absolute -top-1 -right-1 ...">14</div>   {{-- ← natvrdo --}}
</div>

<!-- Mail Button -->
<a href="{{ route('messages.index') }}" ...>
    <img src="{{ asset('images/icons/mail.svg') }}" ...>
    <div class="absolute -top-1 -right-1 ...">654</div>  {{-- ← natvrdo --}}
</a>
```

**Ověřeno v prohlížeči jako přihlášený uživatel:**
```json
{"loggedIn": true, "badges": ["14", "654"], "bellClickable": "no - plain div"}
```

Mobilní menu má stejný problém — `navbar.blade.php:316`: `$mobileMailCount = 654;`.

### 3.2 Nálezy

| ID | Nález | Závažnost |
|---|---|---|
| **F3a** | Badge zvonku je natvrdo `14`. Skutečný počet je 7 notifikací v DB. | **P0** |
| **F3b** | Zvonek je `<div>` **bez `@click`, bez dropdownu** — kliknutí nedělá nic. Uživatel se k notifikacím nedostane jinak než přes `/notifications/archived`. | **P0** |
| **F3c** | Badge pošty je natvrdo `654` (desktop i mobil). Skutečně 0 zpráv. | **P0** |
| **F3d** | **`App\Livewire\NotificationsDropdown` je plně implementovaná a nikde nepoužitá.** Grep přes celý repo (mimo `vendor/`) najde jen definici třídy a její vlastní `render()`. Obsahuje hotové `archive()`, `markAsRead()`, `markAllAsRead()`, `getUnreadCountProperty()`, `getNotificationsProperty()`. | **P0** |

### 3.3 Nálezy uvnitř samotné (nepoužité) komponenty

Až se komponenta zapojí, tyto vady se projeví:

| ID | Nález | Detail |
|---|---|---|
| **F3e** | Blade `notifications-dropdown.blade.php:22` používá `!$notification->read_at` pro zvýraznění nepřečtených. U **globálních** notifikací je `read_at` sdílený sloupec → přečtení jedním uživatelem zhasne notifikaci všem. Model má správný helper `isReadBy($userId)` (`Notification.php:125`), ale blade ho nevolá. Počítadlo `getUnreadCountProperty()` přitom `userStates` řeší správně → **badge a seznam si budou protiřečit**. | **P1** |
| **F3f** | `markAllAsRead()` nemá v bladu žádné tlačítko. | **P2** |
| **F3g** | `markAsRead()` není napojen — kliknutí na notifikaci ji neoznačí přečtenou. Jediná napojená akce je „X" = archivovat. | **P1** |
| **F3h** | Chybí `wire:poll` — badge se neaktualizuje bez reloadu. | **P2** |
| **F3i** | Sloupec `notifications.type` (`info`/`success`/`warning`/`danger`) se plní (12 volání `createForUser`), ale dropdown ho **nikde nezobrazuje** — žádná ikona ani barva. | **P2** |

### 3.4 Backend notifikací je naopak v pořádku

`NotificationController` i model jsou solidní: autorizace přes `authorizeOwnership()`, globální notifikace se nikdy nemažou/needitují na sdíleném řádku, ale přes `NotificationUserState`. Scopy `activeForUser` / `archivedForUser` jsou korektní. **Problém je čistě v prezentační vrstvě.**

Notifikace se generují na 12 místech: schválení/zamítnutí/verifikace profilu, nová zpráva, nové hodnocení, přidání do oblíbených, expirace předplatného, ověření fotky, upozornění adminům na nový profil.

---

## 4. Statický a fabrikovaný obsah — kompletní inventář

Toto je jádro požadavku „žádná sekce nesmí zůstat statická". Nálezy jsou seřazené podle závažnosti.

### F1 — Homepage listing profilů je uměle nafouknutý (**P0**)

`app/Livewire/ProfileList.php:204–246`

```php
if ($this->usesShowcaseProfiles()) {
    $showcaseProfiles = ...->where('content->is_showcase', true)->get();
    if ($showcaseProfiles->count() > 0) {
        $pagesCount = 6;                       // ← natvrdo 6 stránek
        $total = $this->perPage * $pagesCount; // ← fabrikovaný total = 150
        while ($result->count() < $needed) {
            $result = $result->concat($showcaseProfiles); // ← opakování dokola
        }
        return new LengthAwarePaginator($items, $total, ...);
    }
}
```

Bez jediného filtru (výchozí stav homepage) se **5 showcase profilů** opakuje dokola a stránkování hlásí **150 výsledků na 6 stránkách**. V DB je přitom **25 reálných veřejných profilů**.

Ověřeno na živé stránce — Alexandra / Tamara / Jana / Angela / Lucie se opakují v každém bloku, všechny se stejnou hodnotou „168 cm / 19 let".

**Dopad:** návštěvník i provozovatel vidí nepravdivý objem nabídky; reálné profily se na homepage vůbec neukážou; stránkování vede na duplicity.

### F2 — Stránka `/countries` zobrazuje výhradně vymyšlená data (**P0**)

`app/Livewire/CountryProfiles.php:294–351`

```php
private function usesEnglishHomepageMockCountries(): bool
{
    return request()->routeIs('profiles.index') || request()->routeIs('countries.index');
}
```

Podmínka je pravdivá pro **homepage i celou stránku zemí**, takže se **vždy** vrátí `getEnglishHomepageCountries()` — natvrdo napsaný seznam:

```php
['country_code' => 'al', 'country_name' => 'Albánie', 'profiles_count' => 484, ...],
['country_code' => 'cz', 'country_name' => 'Česká republika', 'profiles_count' => 70, ...],
// + regiony Bosny s počty 484, 45, 24, 114, 457, 87, 70, 457, 87
```

a navíc `->concat($repeatedCountries)->concat($repeatedCountries)` — **stejných 8 zemí třikrát za sebou**.

Reálná agregace (`getCountriesProperty()`, řádky 239–292, join `profiles` × `cities` přes `admin_name`) je plnohodnotně napsaná a **je to mrtvý kód** — na těchto dvou routách se nikdy nespustí.

### F3 — Notifikace v hlavičce

Viz kapitola 3.

### F4 — Sekce „Poslední novinky" na homepage je natvrdo (**P0**)

`resources/views/livewire/profile-list.blade.php:722–776` — dvě karty s pevným obrázkem, pevným datem „25. 4. 2025", pevným „5 min čtení", výplňovým textem („Oprávněné aniž i odstoupil o snadno osoby vede grafikou osobami úmyslu 60 %…") a tlačítkem `<button>`, které **nikam nevede**.

Přitom existuje plně funkční `resources/views/components/blog-listing.blade.php`, který umí `$post->hasMedia('header-image')`, `aproximateReadingTime()`, `route('pages.show', $post->slug)` — a v administraci je resource **Blog příspěvky**. Komponenta se na homepage nepoužívá.

### F5 — Detail profilu vymýšlí chybějící data, včetně ceníku (**P0 — obchodní/právní riziko**)

`resources/views/components/profile-detail.blade.php:6–53`

```php
$displayPrices = $prices->isNotEmpty() ? $prices : collect([
    ['time_hours' => 0.5, 'incall_price' => 4000, 'outcall_price' => null],
    ['time_hours' => 1,   'incall_price' => 6000, 'outcall_price' => null],
    ['time_hours' => 2,   'incall_price' => 14000, 'outcall_price' => null],
    ['time_hours' => 3,   'incall_price' => 18000, 'outcall_price' => null],
]);
```

Profil bez ceníku zobrazí **vymyšlené ceny 4 000–18 000 Kč** jako by byly jeho vlastní. Stejný vzorec u:

| Prvek | Fallback |
|---|---|
| Galerie | `$galleryFallbacks` — doplní se `model6.png`, `model10.png`, `model12.png` do 3 slidů |
| Video poster | `model16.png` |
| Služby | `front.profiles.detail_page.services_default` |
| Jazyky | `front.profiles.detail_page.languages_default` |
| Text „o mně" | `front.profiles.detail_page.about_default` |
| Dostupnost | `$availabilityStart = '18'; $availabilityEnd = '18';` |

Naměřený stav dat: z 25 veřejných profilů má **17 vyplněný ceník**, **5 výšku**, první profil má `content = []` → celý blok fyzických parametrů i jazyků je vymyšlený.

### F6 — Karta profilu tvrdí „168 cm" u všech (**P1**)

`resources/views/livewire/profile-card.blade.php:169`

```blade
<div class="flex-1 bg-gray-100 rounded-lg p-3 text-center">
    <div class="text-xs ">168 cm</div>     {{-- natvrdo --}}
</div>
<div class="flex-1 bg-gray-100 rounded-lg p-3 text-center">
    <div class="text-xs ">{{ $profile->age }} {{ __('front.profiles.list.years') }}</div>
</div>
```

Přitom `Profile::getHeightAttribute()` (`Profile.php:109`) čte `content['card_height_cm']` a funguje. Stačí `{{ $profile->height }}`.

Ve variantě `vip-detail` (řádek 53) je navíc fallback hodnocení `'4,9/5'` — profil bez hodnocení předstírá 4,9.

### F7 — `ProfileForm` neumí uložit jazyky (**P1, padají na to testy**)

`ProfileForm` má public property pro `weight_kg`, `height_cm`, `nationality`, `bust_size` — ale **nemá `$languages`**. Model přitom `getLanguagesAttribute()` z `content['languages']` čte a detail profilu ho zobrazuje. Výsledek: jazyky nelze nikdy nastavit, detail vždy spadne na `languages_default`.

Toto je příčina obou selhávajících testů.

### F8 — Statistiky pro poskytovatelku jsou kompletní maketa (**P0**)

`app/Livewire/ProfileStatistics.php:46–72`

```php
$this->chartLabels = ['10. 9.', '11. 9.', ... '25. 9.'];          // pevná zářijová data
$this->chartValues = [38, 42, 64, 60, 22, 38, 43, 64, 44, 68, 96, 104, 66, 84, 83, 36];
```

Stránka `/account/statistics` vykresluje **dvakrát** tuto komponentu (`variant=homepage` a `variant=detail`) — obě s pevnými čísly. Poskytovatelka platí za VIP a vidí smyšlenou návštěvnost.

Navíc `mount()` má fallback `$this->profile = Profile::first();` — uživatel bez profilu uvidí data **cizího** profilu.

Reálná data existují: **30 řádků v `profile_views`**, model má hotové `ProfileView::getDailyStats($profileId, $start, $end, $type)` a `getTotalStats()`. Obě metody jsou **mrtvý kód**.

### F9 — Počty registrovaných v hero sekci jsou natvrdo (**P1**)

| Soubor | Řádek | Hodnota |
|---|---|---|
| `resources/views/profiles/index.blade.php` | 95, 100 | `1 420` dívek / `382` mužů |
| `resources/views/livewire/search-profiles.blade.php` | 959, 964 | `1 420` / `382` |

Skutečnost: **23 žen / 10 mužů**.

Pikantní je, že `ProfileController@index` (řádky 25–26) **správné počty spočítá** a předá do view — a view je ignoruje:

```php
$girlsCount = User::where('gender', 'female')->count();
$gentsCount = User::where('gender', 'male')->count();
return view('profiles.index', compact('profiles', 'girlsCount', 'gentsCount'));
```

Stránka `/countries` přitom stejné proměnné používá **správně** (`countries/index.blade.php:24, 30`). Nekonzistence v rámci jednoho projektu.

### F10 — Patička má všechny odkazy mrtvé (**P1**)

`resources/views/components/footer.blade.php:28–37` — 6× `<a href="#">` (FAQ, Kontakt, Ochrana údajů, Etika, VIP dívky, Premium muži).

Model `Page` má sloupec `display_in_footer` (migrace `2026_01_16_175811`), scope `published()`, `inMenu()` — v patičce se **nepoužívá vůbec nic z toho**. Navigace v hlavičce přitom `$resolvedNavPages` používá správně.

### F11 — Simulovaný online status (**P2 — vědomé rozhodnutí, ale nutno zvážit**)

`app/Models/Profile.php:368–377`

```php
public function isOnline(): bool
{
    if ($this->user?->isOnline()) return true;
    $window = intdiv(time(), 1200);
    return (crc32($this->id . ':' . $window) % 100) < 30;  // ~30 % "online"
}
```

Komentář to přiznává: *„fall back to a simulated status so the site doesn't look empty"*. Deterministické v 20minutovém okně. **Vědomý produktový trik** — chce to rozhodnutí, zda zůstává; pokud ano, měl by být alespoň přepínatelný z administrace, ne zadrátovaný v modelu.

### F12 — Mrtvé proměnné a dvojité počítání impresí (**P1**)

`ProfileController@index` provede kompletní dotaz `getPublicProfiles()`, zavolá `recordListingImpressions($profiles)` a předá `$profiles` do view. View ale renderuje `<livewire:profile-list />`, který si **dotazuje znovu a jinak** (showcase větev). Následek:

- `$profiles` z controlleru je mrtvá proměnná,
- **imprese se zapisují pro jinou množinu profilů, než jaká se uživateli zobrazí** → statistiky v administraci („Zobrazení profilů") jsou nepřesné už u zdroje.

### F13 — Ostatní

| ID | Nález | Závažnost |
|---|---|---|
| F13a | `layouts/account.blade.php:13` — výplňový text „Dokončete registraci Oprávněné aniž i odstoupil o…" natvrdo v layoutu účtu. | P1 |
| F13b | `member-ratings.blade.php` — škála hodnocení 100 % / 70 % / 30 % mapuje na 5 / 4 / 2 hvězdy. Hodnoty 3 a 1 jsou nedosažitelné, průměry v `ratings` jsou tím zkreslené. | P2 |
| F13c | `MemberController@girlsOfMonth` je komentovaný jako „TOP 50 all-time ranking", ale routa i popisek říkají „dívky měsíce" — **žádný měsíční filtr neexistuje**. | P2 |
| F13d | `SubscriptionCheckoutController@success` nevaliduje `session_id` proti Stripe — zobrazí „úspěch" i bez potvrzeného webhooku. | P1 |
| F13e | Nástěnka administrace je prázdná (jen defaultní Filament widgety). Žádný přehled profilů čekajících na schválení, nahlášení, expirujících předplatných. | P1 |
| F13f | `php artisan translations:audit` hlásí **203 podezřelých hardcoded textů** v Blade šablonách (`account/profile/edit.blade.php` je celý anglicky mimo překlady). | P2 |
| F13g | Testy pokrývají prakticky jen Segmenty (poslední feature). **Nulové pokrytí:** notifikace, zprávy, Stripe/předplatné, statistiky, `ProfileList`, autentizace. `/account/subscription` není ani ve `SmokeRoutesTest`. | P1 |

### Shrnutí statického obsahu

| Sekce | Stav | Data existují? |
|---|---|---|
| Homepage — listing profilů | fabrikovaný (5 profilů × 6 stran) | ✅ 25 reálných |
| Homepage — hero počty | natvrdo 1 420 / 382 | ✅ spočítáno, ignorováno |
| Homepage — „Poslední novinky" | 100 % natvrdo | ✅ Page::blog() + `x-blog-listing` |
| Homepage — advert sekce | z `lang/`, needitovatelné z adminu | ⚠️ chybí CMS |
| `/countries` — země a kraje | 100 % natvrdo, 3× zopakováno | ✅ mrtvá agregace v kódu |
| Karta profilu — výška | natvrdo 168 cm | ✅ `Profile::height` |
| Karta profilu — fallback rating | natvrdo 4,9/5 | ✅ `getAverageRating()` |
| Detail profilu — ceník | vymyšlený 4 000–18 000 Kč | ✅ u 17/25 profilů |
| Detail profilu — galerie/služby/jazyky/dostupnost | fallbacky | částečně |
| Statistiky poskytovatelky | 100 % natvrdo | ✅ 30 řádků + mrtvé API |
| Hlavička — notifikace | natvrdo 14 | ✅ 7 v DB + mrtvá komponenta |
| Hlavička — zprávy | natvrdo 654 | ✅ 0 v DB, dotaz existuje |
| Patička — odkazy | 6× `href="#"` | ✅ `Page::display_in_footer` |
| Nástěnka administrace | prázdná | ✅ vše k dispozici |

**Vzorec:** v drtivé většině případů **data i logika už v projektu jsou** — jen prezentační vrstva je odpojená. To je dobrá zpráva: většina oprav je hodiny, ne dny.

---

## 5. Návrh scraperu do administrace

### 5.1 Zadání

Sekce v administraci, kde operátor:
1. zadá URL (jednu, seznam, nebo výpisovou stránku),
2. **v editoru namapuje**, které hodnoty ze stránky se ukládají jako která pole v našem systému,
3. spustí náhled → import,
4. průběžně sleduje běhy, chyby a duplicity.

### 5.2 Datový model

Čtyři nové tabulky:

```
scrape_sources          — zdroj (web), globální nastavení
 ├─ id, name, base_url, list_url_pattern, detail_url_pattern
 ├─ engine              enum: 'http' | 'headless'
 ├─ rate_limit_ms       int (default 2000)
 ├─ user_agent          string nullable
 ├─ is_active           bool
 ├─ respect_robots      bool (default true)
 └─ timestamps

scrape_field_maps       — JEDNO pravidlo mapování (jádro editoru)
 ├─ id, scrape_source_id
 ├─ target_model        enum: 'profile' | 'user' | 'media' | 'service' | 'segment'
 ├─ target_field        string  ('display_name', 'age', 'content.card_height_cm', …)
 ├─ selector            string  (CSS selektor nebo XPath)
 ├─ selector_type       enum: 'css' | 'xpath' | 'regex' | 'json_ld' | 'attribute'
 ├─ attribute           string nullable ('src', 'href', 'content')
 ├─ transformer         json    (řetězec transformací — viz 5.4)
 ├─ is_required         bool
 ├─ sort_order          int
 └─ timestamps

scrape_runs             — jeden běh
 ├─ id, scrape_source_id, started_by (user_id)
 ├─ mode                enum: 'preview' | 'import'
 ├─ status              enum: 'queued'|'running'|'completed'|'failed'|'cancelled'
 ├─ urls_total, urls_done, created_count, updated_count, skipped_count, failed_count
 ├─ started_at, finished_at, error_message
 └─ timestamps

scrape_items            — jedna scrapnutá URL
 ├─ id, scrape_run_id, source_url
 ├─ raw_payload         json  (co se vytáhlo PŘED transformací)
 ├─ mapped_payload      json  (co se uloží PO transformaci)
 ├─ status              enum: 'pending'|'preview'|'imported'|'skipped'|'failed'
 ├─ profile_id          nullable FK (co vzniklo/aktualizovalo se)
 ├─ content_hash        string (pro detekci změn)
 ├─ error_message
 └─ timestamps
```

**Proč `raw_payload` i `mapped_payload`:** když se mapování změní, lze přemapovat bez opětovného stahování webu — to je zásadní pro laděcí smyčku a šetrné k cizímu serveru.

### 5.3 Cílová pole (co lze mapovat)

Odvozeno z reálného schématu `profiles` + `content` JSON:

| Skupina | Cílové pole | Typ |
|---|---|---|
| Základ | `profile.display_name` | string (translatable) |
| | `profile.age` | int |
| | `profile.city` | string → validace proti `cities` |
| | `profile.country_code` | ISO-2 |
| | `profile.address` | string |
| | `profile.about` | text (translatable) |
| Fyzické (`content` JSON) | `content.card_height_cm` | int |
| | `content.weight_kg` | int |
| | `content.bust_size` | A–H |
| | `content.nationality` | ISO-2 |
| | **`content.languages`** | csv — *pozor, viz F7* |
| Kontakt | `user.phone` | string |
| | `content.has_whatsapp` / `has_telegram` | bool |
| Ceník | `profile.local_prices[]` | pole `{time_hours, incall_price, outcall_price}` |
| | `profile.global_prices[]` | totéž v EUR |
| Provoz | `profile.incall` / `outcall` | bool |
| | `profile.availability_hours` | `{always_online, schedule{day:{from,to}}}` |
| Vazby | `services[]` | n:m — párování podle názvu |
| | `segments[]` | n:m |
| Média | `media.profile-images[]` | pole URL → stažení |
| | `media.profile-video` | URL |
| Příznaky | `profile.is_porn_actress` | bool |

### 5.4 Editor mapování — návrh UI

Filament stránka **`ScrapeSourceResource` → záložka „Mapování"**, `Repeater` nad `scrape_field_maps`:

```
┌──────────────────────────────────────────────────────────────────┐
│ Cílové pole      [ Výška (cm) ▾ ]     ← Select z registru polí   │
│ Typ selektoru    [ CSS ▾ ]                                       │
│ Selektor         [ .profile-params li:nth-child(2) .value      ] │
│ Atribut          [ (text)                                      ] │
│ Transformace     [ trim ] → [ regex_extract: (\d+) ] → [ int ]  │
│                  [ + přidat transformaci ]                       │
│ Povinné          [x]                                             │
│ ─────────────────────────────────────────────────────────────── │
│ NÁHLED z testovací URL:                                          │
│   raw:     "  168 cm  "                                          │
│   → trim:  "168 cm"                                              │
│   → regex: "168"                                                 │
│   → int:   168               ✅ platné pro content.card_height_cm │
└──────────────────────────────────────────────────────────────────┘
```

**Klíčová vlastnost — živý náhled.** Nahoře na stránce pole „Testovací URL" + tlačítko **Načíst**. HTML se stáhne jednou, uloží do session, a **každá změna selektoru okamžitě přepočítá náhled** bez dalšího requestu na cizí server.

**Registr transformací** (`app/Services/Scraper/Transformers/`), každá jako samostatná třída s `handle($value, array $config)`:

`trim` · `strip_tags` · `regex_extract` · `regex_replace` · `to_int` · `to_float` · `to_bool` · `lowercase` · `uppercase` · `slug` · `map_values` (slovník např. „Blondýna"→„blonde") · `split` (csv/oddělovač) · `parse_price` · `parse_time_range` · `absolute_url` · `first` · `join` · `default`

### 5.5 Běh scraperu

```
ScrapeSource
    │
    ▼
DiscoverUrlsJob      ── z list_url_pattern vytáhne odkazy na detaily
    │                    (respektuje robots.txt, stránkování)
    ▼
ScrapeUrlJob (× N)   ── queue, throttle dle rate_limit_ms
    │                    stáhne HTML → aplikuje scrape_field_maps
    │                    → uloží raw_payload + mapped_payload do scrape_items
    ▼
[ mode = preview ]   ── STOP. Operátor prochází výsledky v administraci.
    │
    ▼
ImportScrapeItemJob  ── validace → upsert Profile → párování services/segments
    │                    → DownloadMediaJob pro obrázky
    ▼
scrape_runs.completed
```

**Deduplikace:** `content_hash` = SHA-256 z `mapped_payload`. Shodný hash → `skipped`. Párování existujícího profilu podle `source_url` (uložit do `profiles.content['scrape_source_url']`).

**Bezpečnost a etika — nutné zabudovat od začátku:**

- povinná kontrola `robots.txt` (přepínatelná jen se zaškrtnutím a logem, kdo to udělal),
- rate limiting na úrovni zdroje, ne globálně,
- SSRF ochrana — blokovat privátní IP rozsahy, `file://`, redirecty mimo `base_url`,
- max velikost odpovědi a timeout,
- **importované profily vždy `status = 'draft'`, `is_public = false`** — nikdy se nepublikují automaticky; publikaci schvaluje operátor,
- audit log: kdo, kdy, z jakého zdroje, kolik záznamů.

### 5.6 Filament UI — nové položky navigace

Nová skupina **„Scraper"**:

| Položka | Obsah |
|---|---|
| **Zdroje** | CRUD `ScrapeSource`. Tlačítka: *Testovat spojení*, *Náhled (10 URL)*, *Spustit import*. Záložka **Mapování** = editor z 5.4. |
| **Běhy** | Tabulka `ScrapeRun` + progress bar (`urls_done / urls_total`), badge v navigaci = počet běžících. Detail: log, chyby, tlačítko *Zrušit*. |
| **Položky** | Tabulka `ScrapeItem` s filtrem podle stavu. Řádková akce *Zobrazit raw vs. mapped* (side-by-side diff), *Importovat*, *Přeskočit*, *Přemapovat*. |

---

## 6. Propojení napříč systémem

Scraper není izolovaná funkce. Body, které je nutné napojit:

| # | Napojení | Popis |
|---|---|---|
| P1 | **`City` autocomplete** | Scrapnuté město validovat proti 48 059 řádkům `cities`. Neznámé → `scrape_items.status = 'failed'` s návrhem nejbližší shody, ne tiché uložení. Nutné kvůli `Profile::getRegionAttribute()`, který kraj dohledává právě přes `cities.admin_name`. |
| P2 | **`Service` párování** | Scrapnuté názvy služeb mapovat na existující `services` přes slovník `map_values`. Nespárované nabídnout k založení jedním klikem. |
| P3 | **`Segment`** | Automatické přiřazení segmentu podle zdroje (např. „Importováno") pro filtrování na frontendu. |
| P4 | **Media Library** | Stažení obrázků do kolekce `profile-images` + generování konverzí `thumb`/`medium`. **Vyžaduje `ext-gd`** (viz E2). Kontrola MIME proti `acceptsMimeTypes()` v `Profile::registerMediaCollections()`. |
| P5 | **Notifikace** | Po dokončení běhu `Notification::createForUser($operatorId, …)`. Toto je **první reálný důvod, proč musí zvonek v hlavičce fungovat** — dnes by se operátor o dokončení nedozvěděl. Váže na F3. |
| P6 | **Nahrazení showcase mechaniky** | Až budou reálné profily z importu, **F1 (fabrikované stránkování) musí padnout** — jinak importovaná data zůstanou na homepage neviditelná. |
| P7 | **`ProfileView` / statistiky** | Importované profily se musí zahrnout do impresí. Váže na F12 (dvojité počítání) — opravit dřív, než objem dat naroste. |
| P8 | **Shield / oprávnění** | Nové permissions `view_any_scrape_source`, `create_scrape_source`, `run_scraper`, `import_scrape_item`. Scraper nesmí být dostupný běžnému adminovi bez explicitního práva. |
| P9 | **Překlady** | `lang/cs/scraper.php` + `lang/en/scraper.php`. Projekt má `translations:audit` v CI-like skriptu — nové klíče musí existovat v obou jazycích. |
| P10 | **Queue** | Scraper vyžaduje `QUEUE_CONNECTION=database` + běžící `queue:work`. Dnes je v `.env.example` `database`, ale worker není nikde zdokumentovaný ani supervisovaný. Nutné doplnit do deploye. |

---

## 7. Plán vývoje

Pořadí je záměrné: **nejdřív se odstraní lži v datech, pak se staví scraper.** Stavět import do systému, který stejně zobrazuje fabrikovaná data, by byla ztráta času — importované profily by nebyly vidět (F1) a operátor by se o dokončení nedozvěděl (F3).

### Fáze 0 — Stabilizace prostředí (0,5 dne)

- [ ] Zapnout `ext-gd`, přidat do `composer.json` → `require.ext-gd`
- [ ] Doplnit `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` do `.env.example`
- [ ] Opravit `?string $type` v `ProfileView::getDailyStats()` a `getTotalStats()` (PHP 8.4 deprecation)
- [ ] Zdokumentovat SQLite dev cestu v README
- [ ] **Cíl: `php artisan test` zelené kromě F7**

### Fáze 1 — Notifikace v hlavičce (1–1,5 dne) — *explicitní požadavek*

- [ ] Zapojit `<livewire:notifications-dropdown />` do `navbar.blade.php` místo statického `<div>` (F3a, F3b, F3d)
- [ ] Opravit blade na `$notification->isReadBy(auth()->id())` místo `!$notification->read_at` (F3e)
- [ ] Napojit `markAsRead()` na kliknutí + přidat tlačítko „Označit vše jako přečtené" (F3f, F3g)
- [ ] Přidat `wire:poll.30s` na počítadlo (F3h)
- [ ] Zobrazit `type` jako barvu/ikonu (F3i)
- [ ] Nová `MessagesBadge` komponenta — reálný počet nepřečtených z `messages` (F3c), desktop i mobil
- [ ] Testy: `NotificationsDropdownTest` (osobní vs. globální, počítadlo, archivace, per-user read)

### Fáze 2 — Odstranění fabrikovaných dat (3–4 dny)

- [ ] **F1:** odstranit showcase větev z `ProfileList::profiles()`, včetně `usesShowcaseProfiles()` a `content->is_showcase`. Homepage = reálný stránkovaný dotaz.
- [ ] **F2:** odstranit `usesEnglishHomepageMockCountries()` + `getEnglishHomepageCountries()` (~120 řádků), aktivovat existující reálnou agregaci
- [ ] **F4:** nahradit natvrdo napsanou sekci novinek komponentou `<x-blog-listing :posts="Page::blog()->published()->latest()->take(2)->get()" />`
- [ ] **F5:** odstranit `$displayPrices` fallback — profil bez ceníku nezobrazí žádný ceník. Totéž galerie, služby, jazyky, dostupnost. Prázdný stav navrhnout explicitně („Ceník neuveden").
- [ ] **F6:** `168 cm` → `{{ $profile->height }}`, skrýt když `null`. Odstranit fallback `4,9/5`.
- [ ] **F9:** hero počty → `$girlsCount` / `$gentsCount` (už se předávají), stejně v `SearchProfiles`
- [ ] **F10:** patička → `Page::published()->where('display_in_footer', true)->get()`
- [ ] **F13a:** odstranit výplňový text z `layouts/account.blade.php`
- [ ] **F11:** rozhodnutí o simulovaném online statusu — buď zrušit, nebo vytáhnout do nastavení v administraci
- [ ] Testy: `HomepageRealDataTest`, `CountriesRealDataTest`, `ProfileDetailNoFakePricesTest`

### Fáze 3 — Statistiky a integrita dat (2 dny)

- [ ] **F8:** přepsat `ProfileStatistics` na `ProfileView::getDailyStats()`; odstranit `Profile::first()` fallback (uživatel bez profilu → prázdný stav)
- [ ] **F12:** odstranit mrtvý dotaz z `ProfileController@index`; imprese zapisovat **v `ProfileList` na skutečně vykreslené množině**
- [ ] **F7:** přidat `public $languages` do `ProfileForm` + validace + uložení do `content['languages']` → **oba padající testy zezelenají**
- [ ] **F13e:** nástěnka administrace — widgety: profily čekající na schválení, nová nahlášení, expirující předplatná (7 dní), imprese/kliky za 30 dní
- [ ] **F13d:** ověřit Stripe session v `success()` před zobrazením potvrzení

### Fáze 4 — Scraper: základ (4–5 dnů)

- [ ] Migrace: `scrape_sources`, `scrape_field_maps`, `scrape_runs`, `scrape_items`
- [ ] Modely + factories + `ScrapeSourceResource` (základní CRUD)
- [ ] `HttpFetcher` s SSRF ochranou, robots.txt, rate limitem, timeoutem
- [ ] `SelectorEngine` — CSS (`symfony/dom-crawler` + `css-selector`), XPath, regex, JSON-LD
- [ ] Registr transformací (18 tříd dle 5.4) + `TransformerPipeline`
- [ ] `FieldRegistry` — kanonický seznam cílových polí s typy a validátory (5.3)
- [ ] Testy: každá transformace, SSRF ochrana, robots.txt

### Fáze 5 — Scraper: editor mapování (3–4 dny)

- [ ] Filament stránka „Mapování" s `Repeater` nad `scrape_field_maps`
- [ ] Načtení testovací URL do session + **živý náhled bez dalšího requestu**
- [ ] Zobrazení řetězce transformací krok po kroku (raw → … → výsledek)
- [ ] Validace výsledku proti typu cílového pole
- [ ] Import/export mapování jako JSON (přenos mezi prostředími)

### Fáze 6 — Scraper: běh a import (3–4 dny)

- [ ] `DiscoverUrlsJob`, `ScrapeUrlJob`, `ImportScrapeItemJob`, `DownloadMediaJob`
- [ ] Deduplikace přes `content_hash`, párování přes `content['scrape_source_url']`
- [ ] Resources „Běhy" a „Položky" + progress, navigation badge, side-by-side raw/mapped
- [ ] Napojení P1–P4 (města, služby, segmenty, média)
- [ ] Napojení P5 — notifikace o dokončení (staví na Fázi 1)
- [ ] Shield permissions (P8), překlady cs/en (P9)
- [ ] **Importované profily vždy `draft` + `is_public = false`**
- [ ] Dokumentace queue workeru pro deploy (P10)

### Fáze 7 — Dočištění (2 dny)

- [ ] **F13f:** projít 203 hlášení z `translations:audit`, přeložit `account/profile/edit.blade.php`
- [ ] **F13g:** doplnit testy — předplatné, zprávy, `ProfileList`, autentizace, `/account/subscription`
- [ ] **F13b:** rozhodnout o škále hodnocení 100/70/30 → 5/4/2
- [ ] **F13c:** „dívky měsíce" — buď doplnit měsíční filtr, nebo přejmenovat na „TOP 50"
- [ ] Zapnout `translations:audit --strict-hardcoded` jako bránu v CI

**Celkem: 18–24 člověkodnů.**

---

## 8. Doporučené pořadí — proč právě takto

1. **Fáze 1 před scraperem**, protože scraper potřebuje notifikace k oznámení dokončení běhu (P5). Zároveň to byl explicitní požadavek.
2. **Fáze 2 před scraperem**, protože dokud homepage fabrikuje stránkování z 5 showcase profilů (F1), **importované profily nebudou vidět** — scraper by se tvářil jako nefunkční.
3. **Fáze 3 před scraperem**, protože F12 (dvojité počítání impresí) se s rostoucím objemem dat z importu jen zhorší a data v `profile_views` budou tím nepoužitelnější, čím déle se to nechá běžet.
4. **Fáze 4–6 jsou stavitelné inkrementálně** — po Fázi 4 lze scrapovat přes tinker, po Fázi 5 je použitelný editor, po Fázi 6 je hotový produkt.

---

## Přílohy

**A. Reálný stav dat po seedu** (změřeno)

```
profiles_public_approved = 25      (homepage tvrdí 150)
profiles_showcase        = 5
users_female             = 23      (hero tvrdí 1 420)
users_male               = 10      (hero tvrdí 382)
notifications            = 7       (zvonek tvrdí 14)
messages                 = 0       (badge tvrdí 654)
profile_views            = 30      (graf ignoruje, kreslí pevná čísla)
profiles_with_prices     = 17/25   (zbytek dostane vymyšlený ceník)
profiles_with_height     = 5/25    (zbytek dostane "168 cm")
```

**B. Spuštění lokálně**

```bash
cd C:/Users/medion/Desktop/Zasukejsi/ZasukejSi
php artisan serve --host=127.0.0.1 --port=8123
```

Vyžaduje `PHP_INI_SCAN_DIR` s `extension=gd`, dokud se `ext-gd` nezapne v `C:\php\php.ini`.
