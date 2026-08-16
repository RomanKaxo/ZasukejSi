# Audit stavu systému

**Datum:** 16. 8. 2026
**Metoda:** měření, ne odhad — čísla z `route:list`, `artisan test`, `translations:audit` a přímé kontroly nad kódem

---

## 1. Stav v číslech

| Ukazatel | Hodnota |
|---|---|
| Routy | 129 |
| Modely | 26 |
| Filament resources | 24 |
| Migrace | 63 |
| Konzolové příkazy | 10 |
| **Testy** | **239 prošlo** (554 asercí) |
| `translations:audit` | prochází |
| Překladové klíče | cs 1 675 · en 1 678 · **ru 128** |

---

## 2. Nejlépe hodnocené dívky — dokončeno

Sekce byla napojená na databázi (Livewire, ne statická značka), ale nesla tři vady:

| Co | Před | Po |
|---|---|---|
| Řazení sekce „all time" | `rating_this_month` — totéž co sekce nad ní | `rating` |
| Aktuální profil | objevoval se mezi doporučeními sám sobě | vyloučen přes `exclude-profile-id` |
| Poznámka o Premium | vypadala jako odkaz, nikam nevedla, a členovi s Premium tvrdila, že si ho má pořídit | stavově závislá: host → přihlášení, člen bez přístupu → plány, člen s přístupem → datum platnosti |

Prázdný stav i zámek hodnocení pro nečleny už fungovaly.

---

## 3. Co je hotové

**Frontend čte z databáze.** Akceptační kontrola vrací **0 výskytů** vymyšlených dat ve views a **0 mrtvých odkazů** (jediný zbylý `href="#"` je komentář popisující, co tam bylo dřív).

Napojené: hero počty, výpisy, země a kraje, notifikace, zprávy, statistiky, patička, novinky, hodnocení, recenze, členství, ceny ve třech měnách.

**Administrace pokrývá celý datový model.** Z 26 modelů má 24 vlastní resource; `Media` a `NotificationUserState` ho mít nemají a `Setting` má místo CRUD tabulky stránku nastavení.

**Scraper** je hotový včetně revize před importem, respektu ke `crawl-delay` a dohledatelnosti původu fotek.

---

## 4. Co zůstává nedokončené

### Vyžaduje vaše rozhodnutí

1. **Ruština — 128 klíčů z 1 675, tedy 7,6 %.** Existují 2 ze 17 jazykových souborů. Fallback je angličtina, takže návštěvník s ruštinou vidí na 92 % webu angličtinu. Buď doplnit, nebo přepínač skrýt — vlajka vedoucí na angličtinu působí hůř než chybějící jazyk.

2. **Anglická značka.** Figma má pro anglickou verzi **escort-online.com**, implementace používá jedno logo pro všechny jazyky.

3. **Tři osiřelé soubory** bez jediné reference. Ponechány, protože nic nerozbíjejí:
   - `app/Livewire/ProfileRating.php` a jeho šablona — plnohodnotný widget 1–5 hvězd, nikdy nikam nevložený
   - `resources/views/components/blog-listing.blade.php` — novinky se vypisují přímo přes `@forelse`, ne přes tuto komponentu
   - `resources/views/components/autocomplete-select.blade.php` — použitá jen vlastní třídou, kterou nikdo nevolá

   **Oprava dřívějšího tvrzení:** v auditu z 14. 8. jsem uvedl, že `x-blog-listing` je napojená. Není — sekce novinek ji obchází.

4. **Frontendový profilový formulář stále ukládá dostupnost ve starém plochém tvaru.** Normalizace ho při čtení srovná, ale při uložení se tvar znovu rozbije. Oprava znamená zásah do členského formuláře, tedy frontendu.

### Technický dluh

5. **Mobil — jedna oblast ze šesti.** Ověřena homepage, mřížka karet a menu. Neověřen detail profilu (5 rámců), stránky poskytovatelky, členské stránky, modály a výběr země.

6. **Stripe není nakonfigurovaný.** Objednávka nyní selže srozumitelně místo pětistovky, ale bez `STRIPE_SECRET` a `STRIPE_WEBHOOK_SECRET` nelze koupit nic. Bez webhook secretu se předplatné **nikdy neaktivuje** — aktivace je záměrně vázaná na ověřený webhook, ne na návrat z platby.

7. **Chart.js z CDN** — pro produkci lokální build.

8. **B2 a B13** — čekají na potvrzení z Figma Dev Mode.

---

## 5. Vzorec, který se držel celou dobu

Nejčastější nález nebyl chybějící kód, ale **kód, který existoval a nebyl zapojený** — nebo zapojený špatným parametrem:

| Existovalo | Vada |
|---|---|
| `NotificationsDropdown` | nikde nereferencovaná |
| `ProfileView::getDailyStats()` | mrtvý kód, graf kreslil vymyšlená data |
| `PageSeeder` | neregistrovaný → tři „statické" sekce naráz |
| `Subscription::expire()` | bez volajícího → nic nikdy nevypršelo |
| `RoleSeeder.php` | soubor nulové délky → tichý rozpad registrace |
| Slider „all time" | správná komponenta, špatný parametr řazení |
| Ceny předplatného | účtovalo se v korunách bez ohledu na zvolenou měnu |

Doporučení pro další vývoj zůstává: **než se cokoli začne psát nově, ověřit, jestli to už v systému neleží nepoužité nebo špatně nastavené.**

---

## 6. Před nasazením

```bash
git pull origin main
php artisan migrate --force
php artisan db:seed --force
php artisan services:deduplicate
npm run build
php artisan optimize:clear
```

- **`npm run build` je povinný** — `public/build` je gitignorovaný, bez něj se změny v CSS neprojeví
- **`ext-gd`** na produkčním PHP, jinak profily přijdou o fotky
- cron pro `schedule:run`, jinak `subscriptions:lifecycle` nikdy nepoběží
- `queue:work` pod supervisorem
- Stripe klíče včetně webhook secretu
- nastavit simulovaný podíl online profilů (pro ostrý provoz doporučuji `0`)
- srovnat Node na serveru — běží v24, projekt požaduje `>=22 <23`
