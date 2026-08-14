# Závěrečný audit — ZasukejSi

**Datum:** 14. 8. 2026
**Rozsah:** propojení funkcí napříč frontendem, administrací a celým systémem
**Odloženo na přání zadavatele:** B2 (pořadí prvků v kartě), B13 (velikosti nadpisů sekcí)

---

## 1. Stav systému

| Ukazatel | Hodnota |
|---|---|
| Routy | 120 |
| Modely | 20 |
| Livewire komponenty | 17 |
| Filament resources | 21 |
| Filament stránky | 1 (`ManageSettings`) |
| Filament widgety | 1 (`OperationsOverview`) |
| Migrace | 55 |
| Konzolové příkazy | 7 |
| Testy | **153 prošlo** (401 asercí) |
| `translations:audit` | prošel |

Smoke test běžící aplikace: `/`, `/countries`, `/profiles/1`, `/login`, `/register`, `/admin/login` → vše 200.

---

## 2. Co bylo dokončeno v této etapě

### U1 — Životní cyklus předplatných

Nalezený problém: **`Subscription::expire()` neměl žádného volajícího** a překlady
`notifications.*.expiring_soon_*` existovaly, ale nikdy se neodeslaly. Předplatné tedy
nikdy nevypršelo a nikdo nedostal upozornění.

- `ProcessSubscriptionLifecycle` (`subscriptions:lifecycle {--days=7} {--dry-run}`)
- Plánováno denně v 03:10 (`withoutOverlapping()`, `onOneServer()`)
- Sloupce `expiring_notified_at` na obou tabulkách → jedno upozornění za období
- 8 testů

### U2 — Stránka členských plánů

Členské předplatné mělo hotový backend (model, Stripe checkout, admin), ale
**neexistovala obrazovka, kde by si ho člen koupil**. Tlačítko „Začít PRÉMIUM"
mířilo na `#`.

- `resources/views/member/membership.blade.php` + `MembershipCheckoutController@index`
- Routa `account.member.membership.index`
- „Začít PRÉMIUM" v postranním panelu člena nyní míří na plány
- `$premiumUrl` v detailu profilu je kontextové (člen → plány, ostatní → CMS stránka)
- Překlady `front.membership.*` v cs / en / ru

### U3 — Toggle switche ve filtrech

**Zde jsem opravil vlastní dřívější zjištění.** V auditu jsem uvedl, že přepínače
ve filtrech chybí. Byl to omyl mé detekce: hledal jsem `input[type=checkbox]` a
`[role=switch]`, kterým tyto přepínače neodpovídají, protože jsou postavené jako
`<div class="filter-switch">` uvnitř `<button>`.

Ověřeno měřením v prohlížeči: 33×18 px, `#E4E4E7`, aktivní `#DD3888`, posun palce
15 px — desktop i mobil, obojí navázané na Livewire. **Odpovídá Figmě, nic nechybělo.**

Skutečná mezera, kterou to odhalilo: stav přepínače nebyl strojově čitelný.
Doplněno `aria-pressed` na všech 6 pilulkách (3 desktop + 3 mobil), bez vizuální změny.

> Poznámka k měření: `getComputedStyle()` u uzlů, které prošly Livewire morphem,
> vracel v offscreen panelu zastaralé hodnoty. Ověřeno klonem téhož tlačítka
> vloženým do stejného rodiče — ten se vykreslil správně růžově. Nešlo tedy o
> chybu v CSS, ale o artefakt vykreslovacího prostředí.

### Stránka „Recenze" (poskytovatelky)

Poslední mrtvý odkaz v systému. `href="#"` + `!text-gray-400` + placeholder
„Připravujeme" — **přestože routa `account.reviews`, controller i hodnocení v DB
existovaly.**

- `AccountController@showReviews` načítá reálná hodnocení (`with('user')`, stránkováno po 20)
- Dlaždice průměru a počtu podle konvence prázdných hodnot (dlaždice zůstává, hodnota → „—")
- Odkaz v postranním panelu připojen a odšedivěn
- Překlady `front.account.reviews.*` v cs / en / ru
- 3 testy

### Simulovaný online stav

Poslední zdroj vymyšlených dat v systému: `crc32(...) % 100 < 30` natvrdo v modelu,
tedy ~30 % profilů předstíralo online stav.

- Přesunuto do `config/site.php` → `online_simulation_percent` (env `ONLINE_SIMULATION_PERCENT`)
- **`0` simulaci úplně vypne** a odznak pak odráží jen skutečnou aktivitu
- Skutečná aktivita má i nadále přednost
- 3 testy

---

### Hodnocení — procento jako uložená pravda

Původní stav: členské UI sbírá 100 / 70 / 30 %, ale ukládala se jen hvězda přes
mapování `100→5, 70→4, 30→2`.

Dvě prokazatelné vady, které z toho plynuly:

1. **Hodnoty 3 a 1 hvězdy byly nedosažitelné**, takže všechny průměry v
   `profile_ratings` byly zkreslené.
2. **Členská historie kreslila špatná procenta.** Řádek
   `member-ratings.blade.php:363` převáděl hvězdu zpět jako `rating / 5 * 100`,
   takže kdo klikl na **70 %, viděl v proužku 80 %**, a kdo na 30 %, viděl 40 %.

Řešení: procento se ukládá tak, jak bylo zvoleno.

| Vrstva | Změna |
|---|---|
| `profile_ratings.percentage` | nový sloupec, migrace zpětně dopočítá 5→100, 4→70, 2→30 |
| `profile_ratings.rating` | zůstává jako celohvězdičkové zrcadlo pro řazení a administraci |
| `Rating::booted()` | drží oba sloupce v souladu, ať zápis přijde odkudkoli |
| `Profile::rateByPercentage()` | nová autoritativní metoda; `rateBy()` zůstává jako obal |
| `Profile::getAverageRating()` | počítá z procent → 70 % je nyní 3,5/5, ne 4/5 |
| `withAvg` na 7 místech | přepnuto na `percentage`, řazení tedy odpovídá zobrazovaným hodnotám |
| `RatingFactory` | procento je základ, hvězda se dopočítá |

Ověřeno testem: dvě hodnocení 70 % a 50 % dají průměr přesně **3,0/5** — hodnotu,
kterou původní mapování nedokázalo vyprodukovat.

### Správa v administraci

Vznikla tabulka `settings` (klíč / hodnota) a stránka **Nastavení → Nastavení systému**:

- **Škála hodnocení** — všechny tři procentní hodnoty tlačítek
- **Simulovaný podíl online profilů** — `0` simulaci vypne

Tlačítka v členském UI i validace v `MemberRatings::rateProfile()` čtou hodnoty
z nastavení, takže není možné odeslat procento, které UI nenabízí.

Ověřeno v běžící aplikaci: změna středního stupně na 75 % v administraci se
okamžitě projevila na `/account/member/ratings` jako `rateProfile(75)` a popisek
„75 %" na desktopu i mobilu. Hodnota byla poté vrácena na 70 %.

Administrační tabulka hodnocení nyní ukazuje procento jako hlavní údaj (barevně
podle prahů) a vedle něj hvězdy s přesnou hodnotou v pětkové škále.

`Setting::get()` má stejnou ochranu jako překladový loader — pozitivní výsledek
kontroly existence tabulky se latchuje, negativní nikdy, aby to nespadlo při
prvním volání před doběhnutím migrací.

---

## 3. Akceptační kritérium — vymyšlená data

```bash
grep -rnE '\b(168 cm|4[.,]9/5|1 420|382|654|4000|18000)\b' resources/views --include=*.blade.php
```

Vrací **pouze komentáře**, které dokumentují, co na daném místě dřív bylo. Žádný
výskyt v roli dat.

Poslední porušení bylo v `resources/views/livewire/profile-card.blade.php` — osiřelém,
nikde nereferencovaném souboru. Podle pravidla R1 jsem ho nesmazal, ale hodnoty
`4,9/5` a `168 cm` jsem nahradil reálnými daty s prázdným stavem, aby soubor
nebyl v rozporu se zbytkem systému.

---

## 4. Opakující se vzorec napříč celým projektem

Nejdůležitější zjištění celé práce: **data a logika už existovaly, chyběla jen
prezentační vrstva.** Doložené případy:

| Co existovalo | Co chybělo |
|---|---|
| `NotificationsDropdown` (kompletní komponenta) | nikde nereferencovaná |
| `ProfileView::getDailyStats()` | mrtvý kód, graf kreslil vymyšlená data |
| `PageSeeder` | neregistrovaný v `DatabaseSeeder` → tři „statické" sekce naráz |
| `$girlsCount` / `$gentsCount` | controller je počítal, view je ignorovala |
| `Page::display_in_footer` | sloupec i scope bez jediného použití |
| `x-blog-listing` | komponenta bez použití, novinky psané natvrdo |
| `Subscription::expire()` | bez volajícího → nic nikdy nevypršelo |
| `account.reviews` + controller + data | odkaz vedl na `#`, stránka byla placeholder |
| Členské předplatné (model, Stripe, admin) | žádná obrazovka pro nákup |

Z toho plyne doporučení pro další vývoj: **než se cokoli začne psát nově, ověřit,
zda to už v systému neleží nepoužité.**

---

## 5. Otevřené body

### Vyžadují vaše rozhodnutí

1. **Dva osiřelé soubory** bez jediné reference. Ponechány podle pravidla R1, obsah
   v nich je konzistentní se zbytkem systému. Smazat?
   - `resources/views/livewire/profile-card.blade.php` (chybí třída `App\Livewire\ProfileCard`)
   - `app/Livewire/ProfileRating.php` + jeho šablona — plnohodnotný widget 1–5 hvězd,
     který se nikdy nikam nevložil; jediná cesta k hodnocení vede přes členskou stránku

2. **Rozšíření tří stupňů hodnocení.** Škála je nyní procentní a přesná, ale UI
   nabízí tři přednastavené hodnoty, protože tak je navržena Figma. Pokud byste
   chtěli jemnější škálu, je datová vrstva na to připravená — jde už jen o návrh
   obrazovky.

### Odloženo vámi

4. **B2** — pořadí prvků v kartě. Figma si v tomto bodě odporuje mezi hlavní mřížkou
   a druhou řadou; bez upřesnění nelze rozhodnout, která varianta je závazná.
5. **B13** — velikosti nadpisů sekcí (~24 px vs. 34 px). Odvozeno z rozměrů textových
   rámců, potřebuje potvrzení z Dev Mode.

### Známé mezery

6. **Mobilní verze neověřena.** Figma obsahuje 27 rámců `phone 360 px`, které nebyly
   porovnány s implementací. Desktop odpovídá; mobil je otevřené riziko.
7. **Chart.js z CDN** (`profile-statistics.blade.php:151`). Pro produkci doporučuji
   lokální build kvůli závislosti na třetí straně.
8. **Scraper** — odložen na samostatnou etapu po nasazení podle vaší volby.
   Návrh (4 tabulky, vizuální editor mapování polí) je v
   `docs/audit/2026-08-14-audit-a-plan-scraperu.md`.

---

## 6. Před nasazením

- `queue:work` pod supervisorem (notifikace, media library)
- `subscriptions:lifecycle` běží přes `schedule:run` — ověřit cron na serveru
- `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` v produkčním `.env`
- Migrace na MySQL/MariaDB (vývoj běží na SQLite)
- `ext-gd` na produkčním PHP (lokálně řešeno obejitím)
- Nastavit simulovaný podíl online profilů v administraci (pro ostrý provoz
  doporučuji `0`); `ONLINE_SIMULATION_PERCENT` v `.env` slouží už jen jako
  výchozí hodnota, než se nastavení poprvé uloží
