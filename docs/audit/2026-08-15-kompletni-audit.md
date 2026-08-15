# Kompletní audit systému ZasukejSi

**Datum:** 15. 8. 2026
**Rozsah:** všechny funkce, frontend, administrace, scraper

---

## 1. Stav v číslech

| Ukazatel | Hodnota |
|---|---|
| Routy | 126 |
| Modely | 25 |
| Livewire komponenty | 17 |
| Filament resources | 23 |
| Filament stránky / widgety | 1 / 1 |
| Migrace | 57 |
| Seedery | 17 |
| Konzolové příkazy | 9 |
| Servisní třídy | 12 |
| Testovací soubory | 39 |
| **Testy** | **179 prošlo** (460 asercí), 31 s |
| `translations:audit` | prochází |

---

## 2. Frontend

### Hotovo

Původní stav byl „hotový frontend, hotová data, mezi tím nic". To je uzavřené — každá sekce čte z databáze:

| Oblast | Zdroj dat |
|---|---|
| Hero počty | `users` podle pohlaví |
| Výpis a slidery | `profiles` se skutečným stránkováním |
| Země a kraje | `countries` + `cities.admin_name`, počty z DB |
| Notifikace a zprávy | reálné počty, per-user stav přečtení |
| Statistiky | `profile_views` přes `getDailyStats()` |
| Patička a menu | `pages` s `display_in_footer` / pořadím |
| Novinky | `pages` typu blog |
| Hodnocení | `profile_ratings`, procenta jako uložená pravda |
| Recenze poskytovatelky | reálná hodnocení se stránkováním |
| Členství | `member_subscriptions` + Stripe |

**Akceptační kontrola:** grep na vymyšlená data ve views vrací **0 výskytů**. Mrtvé odkazy `href="#"`: **0** (dva zbývající výskyty jsou komentáře popisující, co tam bylo dřív).

### Neuzavřeno

**Mobil — hotová jedna oblast ze šesti.** Proti 31 rámcům z Figmy je ověřená homepage, mřížka karet a menu. Neověřeno zůstává:

1. Detail profilu (5 rámců) — nejsložitější obrazovka
2. Stránky poskytovatelky — služby a ceny, statistiky, fotografie, základní údaje
3. Členské stránky — nástěnka, nahlášené dívky
4. Modály — registrace (3), login, věková brána, lightbox
5. Výběr země a města (3 rámce)

**Ruština na 8 %.** Změřeno: 125 klíčů z 1635 (cs 1635, en 1638). Existují 2 ze 17 jazykových souborů. Fallback je angličtina, takže návštěvník s ruštinou vidí na 92 % webu angličtinu.

**B2 a B13** odloženy vámi — pořadí prvků v kartě (Figma si odporuje) a velikosti nadpisů sekcí (nutné potvrzení z Dev Mode).

---

## 3. Administrace

**Pokrytí je úplné.** Z 25 modelů má 22 vlastní resource. Zbylé tři ho mít nemají:

- `Setting` — má místo CRUD tabulky stránku *Nastavení systému*
- `Media` — interní spatie medialibrary, spravuje se přes formuláře profilů a stránek
- `NotificationUserState` — spojovací tabulka stavů přečtení

### Skupiny v menu

| Skupina | Obsah |
|---|---|
| Obsah | Profily, Stránky, Blog, Segmenty, Služby |
| Uživatelé | Uživatelé, Role, Zprávy, Notifikace |
| Hodnocení a hlášení | Hodnocení, Hlášení, Nahlášené profily |
| Předplatné | Předplatná, Členská předplatná, Typy, Logy |
| Nastavení | Země, Města, Překlady, Nastavení systému |
| Scraper | Zdroje, Ke kontrole, Historie běhů |

### Co lze z administrace řídit

- **Překlady** — každý `__()` řetězec, override nad soubory v `lang/`
- **Škála hodnocení** — všechny tři procentní hodnoty tlačítek
- **Simulovaný online stav** — 0 simulaci vypne
- **Země** — pořadí, viditelnost, přepis názvu; počty jsou read-only z DB
- **Pořadí stránek** v menu a patičce
- **Scraper** — zdroje, selektory, schvalování položek

---

## 4. Scraper

Postavený jako obecný nástroj: **nový web je řádek v databázi a sada selektorů, ne nová třída.**

```
scrape_sources        web, adaptér, nastavení
  └─ scrape_field_maps   selektor → naše pole (+ transformace)
scrape_runs           historie běhů
scrape_items          jeden profil, čeká na kontrolu
```

### Ověřeno proti živé stránce

Z ukázkového profilu se bere **19 polí**: věk, výška, váha, poprsí a jeho typ, národnost, jazyky, barva očí a vlasů, délka vlasů, ochlupení, cestování, pohlaví, město, země — plus **28 služeb** a **10 fotografií** ve variantě 1000×700.

### Pojistky

| Co | Jak |
|---|---|
| Nic se nepublikuje samo | Položka → schválení → nepublikovaný profil ve stavu ke schválení |
| Prodleva | Vyšší z „nastavení zdroje" a „`Crawl-delay` z robots.txt" |
| robots.txt | Čte se každý běh a ukládá ke zdroji |
| Vypnutý zdroj | Nelze stáhnout ani z administrace, jen `--dry-run` |
| Původ | Položka drží zdrojovou URL, každá fotka adresu v `custom_properties` |
| Číselník služeb | Scrapnutý název se páruje jen na existující službu, nové nezakládá |

---

## 5. Opakující se vzorec

Nejdůležitější zjištění celé práce: **data a logika existovaly, chyběla prezentační vrstva.** Doložené případy:

| Existovalo | Chybělo |
|---|---|
| `NotificationsDropdown` | nikde nereferencovaná |
| `ProfileView::getDailyStats()` | mrtvý kód, graf kreslil vymyšlená data |
| `PageSeeder` | neregistrovaný → tři „statické" sekce naráz |
| `Subscription::expire()` | bez volajícího → nic nikdy nevypršelo |
| `account.reviews` + controller + data | odkaz vedl na `#`, stránka placeholder |
| Členské předplatné (model, Stripe, admin) | žádná obrazovka pro nákup |
| `RoleSeeder.php` | soubor nulové délky od prvního commitu |

Doporučení pro další vývoj zůstává: **než se cokoli začne psát nově, ověřit, jestli to už v systému neleží nepoužité.**

---

## 6. Otevřené body

### Vyžadují vaše rozhodnutí

1. **Ruština** — doplnit překlad, nebo přepínač skrýt? Vlajka vedoucí na angličtinu působí hůř než chybějící jazyk.
2. **Anglická značka** — Figma má pro anglickou verzi **escort-online.com**, implementace používá jedno logo pro všechny jazyky.
3. **Statistiky na mobilu** — Figma kreslí vodorovné pruhy s datem vlevo, implementace používá Chart.js.
4. **Dva osiřelé soubory** — `livewire/profile-card.blade.php` a `app/Livewire/ProfileRating.php` (plný widget 1–5 hvězd, nikdy nikam nevložený). Ponechány, smazat?
5. **Zapnutí scraperu** a právní základ zpracování osobních údajů a autorských práv k fotografiím.

### Technický dluh

6. **Mobil** — pět z šesti oblastí neověřeno (viz oddíl 2)
7. **Chart.js z CDN** — pro produkci lokální build
8. **B2 a B13** — čekají na Dev Mode

---

## 7. Před nasazením

- `php artisan migrate --force && php artisan db:seed --force` na MySQL (databáze je prázdná)
- `php artisan users:set-password` — hesla nejsou na serveru nastavená; seed dává adminovi `admin123`, ostatním `password`
- `npm run build` — `public/build` je gitignorovaný, CSS změny se bez buildu neprojeví
- `queue:work` pod supervisorem
- cron pro `schedule:run`, jinak `subscriptions:lifecycle` nikdy nepoběží
- produkční Stripe klíče a registrace webhooku
- **`ext-gd`** — bez něj profily přijdou o fotky (seed už kvůli tomu nespadne, ale fotky nevzniknou)
- nastavit simulovaný podíl online profilů (pro ostrý provoz doporučuji `0`)
