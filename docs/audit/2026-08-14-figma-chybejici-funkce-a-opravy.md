# Figma → implementace: chybějící funkce a plán oprav

**Datum:** 14. 8. 2026
**Podklad:** export `C:\Users\medion\Desktop\xxx` (72 rámců, měřítko 1:1) + metadata Figma MCP pro frame „VIP"
**Stav kódu:** nezměněn — dokument je podklad pro rozhodnutí, ne provedená práce.

---

## Shrnutí

Návrh počítá s **placenou členskou vrstvou pro muže**, kolem které je postavená celá mechanika zamykání obsahu. **Tato vrstva v systému neexistuje** — ani datově, ani funkčně. Všechno ostatní jsou dílčí odchylky.

| Kategorie | Počet | Nejvyšší závažnost |
|---|---|---|
| Chybějící funkce | 6 | P0 — blokuje obchodní model |
| Vizuální odchylky | 17 | P2 |
| Mrtvé odkazy | 6 | P1 |
| Natvrdo zapsaná data | 1 blok × 5 souborů | P1 |

---

# ČÁST A — Chybějící funkce

## A1 · Premium členství pro muže — **P0, neexistuje**

### Důkazy z návrhu

| Kde | Co návrh ukazuje |
|---|---|
| `muž - záložky*` (5 rámců) | zelený pruh „🏆 Vaše Premium členství platí do 12. 12. 2025" s křížkem |
| `VIP.jpg` (Frame 688, 1040) | pruh 472 × 49 „Premium účet vám odemkne hodnocení" |
| `home.jpg`, karty | hodnocení nahrazené **ikonou zámku** |
| `VIP.jpg` (Frame 1067) | tlačítko „Obnovit přístup" 231 × 59 |
| filtry | položka „Rating" s ikonou zámku |

### Stav v kódu

```
database/migrations/2026_01_16_000002_create_subscriptions_table.php:13
    $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
```

Předplatné je vázané na **`profiles`**, a profil má podle `routes/web.php` jen **žena**. Muž-člen nemá k předplatnému žádnou vazbu — model, migrace ani UI neexistují.

```
resources/views/components/member-sidebar.blade.php:111
    <a href="#" …>{{ __('front.account.sidebar.premium_button') }}</a>   ← „Začít PRÉMIUM" nikam nevede
```

### Co je potřeba doplnit

1. Datový model členského předplatného pro `users` — buď nová tabulka `member_subscriptions`, nebo zobecnit `subscriptions` na polymorfní vazbu (`subscribable_type` / `subscribable_id`). **Doporučuji novou tabulku** — nerozbije stávající dotazy nad `subscriptions.profile_id`, kterých je v kódu 11.
2. Typy členského předplatného + CRUD v administraci (obdoba `SubscriptionTypeResource`).
3. Napojení na Stripe checkout — `SubscriptionCheckoutController` je dnes vázaný na `gender:female` + `profile.exists`; potřebuje druhou větev pro muže.
4. Metoda `User::hasActiveMembership()` a `Gate` pro odemčený obsah.
5. Banner z reálného `ends_at` (viz A6).

**Odhad:** 4–6 dnů. Bez toho nemá smysl A2.

---

## A2 · Zamykání hodnocení — **P0, implementováno obráceně**

| | Návrh | Implementace |
|---|---|---|
| Fotka | **ostrá** u všech karet | **rozmazaná** u ~poloviny |
| Hodnocení | **zamčené** (růžový zámek) | **viditelné** (`4.8/5`) |
| Pozadí pilulky | #D9D9D9 / #E8E8E8 dvoutónově | #E6FEE8 zelená |

```
app/Livewire/ProfileList.php
    $isOpenProfile = crc32((string) $profile->id) % 2 === 0;
    $cardVariant = (auth()->check() || $isOpenProfile) ? null : 'vip-detail';
```

Náhodné rozmazání každé druhé fotky návrh nikde nemá. Zamyká se **konzistentně hodnocení**, a odemyká ho Premium (A1).

**Postup bez poškození:**
- Zavést `x-profile-card` prop `ratingLocked` místo současné `vip-detail` větve, výchozí hodnota podle `Gate`.
- Variantu `vip-detail` **neodstraňovat** — používá ji `profile-detail.blade.php:2907` pro slidery.
- Pokryto testem `ProfileCardRealDataTest`, rozšířit o případ zamčeného hodnocení.

---

## A3 · Ruský jazyk — **P1, chybí**

Mobilní menu (`menu default.jpg`, `menu logged-in muž.jpg`) má **tři jazyky**: Česky · English · **Русский**, včetně vlajek.

```
app/Http/Middleware/SetLocale.php:27
    if (in_array($locale, ['en', 'cs'])) {
lang/  →  cs, cs.json, en, en.json          ← ru chybí
```

**Potřeba:** `lang/ru/*` (10 souborů), rozšířit whitelist, vlajka `public/flags/ru.png`, přidat do přepínače v `navbar.blade.php` i `footer.blade.php`.
Projekt má `translations:sync-en` přes DeepL — stejný postup lze použít pro ruštinu.

---

## A4 · Přepínače ve filtrech — **P2, komponenta existuje a nepoužívá se**

Návrh má u „Verified photo", „Video" a „Porno actress" **skutečné toggle switche**. V DOM není ani jeden `input[type=checkbox]` ani `role="switch"`.

`resources/views/components/toggle-switch.blade.php` **existuje** a používá se v `profile-form.blade.php` (4×) a `services-manager.blade.php` (3×). Ve filtrech ne.

**Postup:** použít stávající komponentu, nevytvářet druhou.

---

## A5 · Mrtvé odkazy — **P1**

| Soubor | Prvek | Stav |
|---|---|---|
| `profile-detail.blade.php:2513, 2637` | „Obnovit přístup" | `href="#"` |
| `profile-detail.blade.php:2514, 2641` | „Dát hodnocení" | `href="#"` |
| `member-sidebar.blade.php:111` | „Začít PRÉMIUM" | `href="#"` |
| `auth/register.blade.php:115, 117` | Obchodní podmínky, Ochrana osobních údajů | `href="#"` — **přitom `/terms-of-service` a `/privacy-policy` už existují** |

`account-sidebar.blade.php:75` („Recenze") je `href="#"` **záměrně** — překlad zní „Recenze - již brzy" a odkaz má `!text-gray-400`. Nechat být.

---

## A6 · Premium banner s natvrdo zapsaným datem — **P1**

```
resources/views/member/archive.blade.php:9         Vaše Premium členství platí do 12. 12. 2025
resources/views/member/favorites.blade.php:9       Vaše Premium členství platí do 12. 12. 2025
resources/views/member/girls-of-month.blade.php:9  Vaše Premium členství platí do 12. 12. 2025
resources/views/member/ratings.blade.php:9         Vaše Premium členství platí do 12. 12. 2025
resources/views/member/reported.blade.php:8        Vaše Premium členství platí do 12. 12. 2025
```

Pětkrát duplikovaný 15řádkový blok s **pevným datem** a **nepřeloženým textem**. Každému členovi tvrdí totéž bez ohledu na realitu — a předplatné pro muže vůbec neexistuje (A1).

**Postup:** vytáhnout do `x-premium-banner` a vykreslovat jen když má uživatel aktivní členství, s reálným `ends_at`. Vloží se do `layouts/member.blade.php`, čímž zmizí duplicita z 5 souborů.

---

# ČÁST B — Vizuální opravy

Hodnoty jsou změřené, ne odhadnuté. Pořadí = doporučené pořadí provádění.

## Úvodní stránka

| # | Co | Návrh | Implementace | Kde |
|---|---|---|---|---|
| B1 | Pořadí v menu | Úvod · **VIP a Premium** · **FAQ** · Etika · Kontakt | Úvod · FAQ · VIP a Premium · … | `PageSeeder` — řadí se dle `created_at` |
| B2 | Pořadí v kartě | Detail → **Hodnocení** → Lokalita → **168 cm/19 let** | Detail → 168 cm/19 let → Lokalita → Hodnocení | `components/profile-card.blade.php` |
| B3 | Výška filtrační pilulky | **33 px** | 41 px | filtry v `profile-list.blade.php` |
| B4 | Mezera mezi kartami | **21,5 px** (sloupec 210) | 23,6 px (sloupec 217,6) | mřížka `profile-list.blade.php` |
| B5 | Výchozí kraj | **Hlavní město Praha** | Praha | `SearchProfiles::getAllRegionsProperty()` |
| B6 | Výchozí věk | **18 let** | 18-25 let | `SearchProfiles::mount()` |

> **B2 — pozor:** návrh si odporuje. Hlavní mřížka pod „Nejlepší výsledky" má hodnocení na 3. pozici, druhá řada na téže stránce na 5. Před opravou potřebuji vědět, která varianta platí.

## Detail profilu

| # | Co | Návrh | Implementace |
|---|---|---|---|
| B7 | Mezera mezi dlaždicemi galerie | **10 px** | 14 px |
| B8 | Výška řádku ceníku | **36 px** | 45,5 px |
| B9 | Rozteč řádků parametrů | **36 px** | 21,6 px |
| B10 | Šířka obsahu levého panelu | **231,47 px** | 237 px |
| B11 | Šířka video bloku | **254,24 px** | 242 px |
| B12 | Šířka textu „o mně" | **844,1 px** | 820 px |
| B13 | Velikost nadpisů sekcí | **~24 px** pro „Více o mně / Video / Moje ceny / Služby", 34 px pro slidery | 34 px na obojí |

> **B13** je odvozeno z rozměru textového boxu, ne z hodnoty `font-size`. Před opravou potvrdit z Dev Mode.

## Co je naopak správně — **neměnit**

Karta 210 × 520 · obrázek 210 × 265 · Detail 170 × 45 · pilulky 82 × 30 · VIP odznak 50 × 26 · „VIP PROFIL" 93 × 30 · InCall/OutCall 113 × 40 · galerie 337/537/337 × 537 · ceník 526 px · panel 261 px · eco pruh 1136 × 35.

**Barvy sedí všechny:** #DD3888 · #5C2D62 · #FFB700 · #CDEFD0 · #F2F2F2. Odchylky 1–2 jednotky ve vzorcích jsou artefakt JPEG komprese.

---

# ČÁST C — Jak to udělat bez poškození

1. **Nejdřív A1**, pak A2. Zamykání hodnocení nemá smysl implementovat, dokud neexistuje, co ho odemyká.
2. **Nesahat na `vip-detail` variantu** `x-profile-card` — používá ji slider na detailu profilu. Zamykání řešit novým propem.
3. **Nová tabulka místo úpravy `subscriptions`** — `subscriptions.profile_id` figuruje v 11 místech kódu včetně `Profile::isVip()`, `ProfileList` řazení a Filament resources.
4. **Premium banner vytáhnout do komponenty** a vložit do `layouts/member.blade.php` — odstraní pětinásobnou duplicitu jedním zásahem.
5. **Po každé sadě spustit** `php artisan test` (103 testů) **a** `php artisan translations:audit`.
6. **B1 (pořadí menu)** neřešit změnou seederu na produkci — pořadí patří do administrace. `Page` nemá sloupec `sort_order`; doporučuji ho doplnit, jinak bude pořadí navždy záviset na `created_at`.

---

# ČÁST D — Co zbývá zkontrolovat

Prošel jsem **10 rámců ze 72**. Neověřeno zůstává:

| Skupina | Ks | Riziko dalších nálezů |
|---|---|---|
| `phone 360 px*` | 27 | **vysoké** — celá mobilní verze, zatím bez jediné kontroly |
| `home-1` … `home-25` | 25 | střední — stavy, modály, rozbalená menu |
| `VIP-1..3`, `ne VIP` | 4 | střední — varianty detailu profilu |
| `muž - záložky-1..4` | 4 | střední — zbylé členské záložky |
| `lightbox-1..3` | 3 | nízké — základ ověřen, thumbnaily i šipky v kódu jsou |
| `vybrané město v zemi*` | 2 | nízké |

Mobilní verze je největší nepokrytá oblast — 27 rámců proti 10 zkontrolovaným.
