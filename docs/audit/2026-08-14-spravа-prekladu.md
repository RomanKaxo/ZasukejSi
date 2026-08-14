# Správa překladů, ruština a pořadí menu

**Datum:** 14. 8. 2026
**Testy:** `128 prošlo (321 assertions)` — přibylo 12. `translations:audit` prochází.
**V databázi:** 3 415 překladových řetězců (cs 1 646 · en 1 649 · ru 120) v 18 skupinách.

---

## 1. Překlady spravovatelné v administraci

### Proč to řeší víc než jen překlady

Každý text na webu jde přes `__()`, ale hodnoty žily jen v `lang/*.php`. Hero text, advert blok, eco pruh i všechny popisky tlačítek se tak daly změnit **jen editací souborů a nasazením**. Administrace se k nim nedostala vůbec.

Tím, že jsou překlady nyní v databázi, se **všechny tyto texty staly editovatelnými naráz** — nebylo potřeba pro každý zvlášť vymýšlet nastavení.

### Jak to funguje

| Vrstva | Role |
|---|---|
| `lang/*.php` | výchozí hodnoty — to, co se nasazuje, co kontroluje `translations:audit` a na co se padá |
| tabulka `translations` | přepisová vrstva nad soubory, jeden řádek = jeden klíč pro jeden jazyk |
| `DatabaseTranslationLoader` | rozšiřuje `FileLoader`, slučuje přepisy nad soubory |

Loader je zaregistrovaný přes `$this->app->extend('translation.loader', …)`, takže se zachová veškeré Laravelí nastavení cest a jmenných prostorů.

**Odolnost:** když se dotaz nepovede, loader se tiše vrátí k souborovým hodnotám — výpadek databáze nesmí shodit stránku kvůli popisku.

### Administrace

`Nastavení → Překlady`:

- editace **přímo v tabulce** (`TextInputColumn`) — přepis webu je hodně malých úprav, otevírat formulář pro každou by zdržovalo
- filtry: jazyk, soubor, **jen upravené**, **nepřeložené**
- fulltext přes klíč i hodnotu
- akce **Vrátit výchozí** (jednotlivě i hromadně)
- tlačítko **Načíst nové texty** — spustí import po nasazení
- tlačítko **Vyprázdnit cache**

Zakládání nových řádků je vypnuté: ručně vytvořený klíč by nikde v kódu nebyl čtený. Řetězce přicházejí importem.

### Příkaz

```bash
php artisan translations:import                 # všechny jazyky
php artisan translations:import --locale=ru     # jen ruštinu
php artisan translations:import --group=front   # jen jeden soubor
php artisan translations:import --force         # přepíše i ručně upravené
php artisan translations:import --prune         # smaže klíče, které už v souborech nejsou
```

**Úpravy z administrace import nikdy nepřepíše** — bez `--force` se aktualizuje jen `default_value`, aby bylo vidět, k čemu se lze vrátit.

---

## 2. Dvě chyby, které se přitom našly a opravily

### Přepis se neprojevil v témže requestu

`Cache::forget()` nestačil. Laravel si drží načtené skupiny **ve dvou dalších vrstvách**: loader má vlastní memo a `Translator` si pamatuje, které skupiny už načetl, a podruhé se loaderu nezeptá.

Bez vyčištění obojího by se úprava uložená v administraci projevila **až při dalším requestu** — tedy operátor by uložil a viděl starou hodnotu. `Translation::flushCache()` nyní čistí všechny tři vrstvy.

### Loader si napevno zapamatoval chybějící tabulku

Loader je singleton vytvořený při bootu. Původně si výsledek `Schema::hasTable('translations')` uložil natrvalo — a protože pod `RefreshDatabase` běží migrace **až po** bootu, latchoval `false` na celý testovací běh a žádný přepis se nikdy neuplatnil.

Nyní se pamatuje **jen kladný výsledek**: tabulka může během života procesu vzniknout, ale nikdy nezmizí.

---

## 3. Ruština

- `lang/ru/front.php` + `lang/ru/common.php` — navigace, hero, vyhledávání, výpis, patička, detail profilu, členská sekce, notifikace
- `public/flags/ru.png`
- 120 řetězců naimportováno, spravovatelných v administraci

**Nepřeložené klíče padají na `fallback_locale`**, takže web funguje celý — nezobrazují se syrové klíče. Zbytek lze doplnit přímo v administraci, bez zásahu do kódu.

### Audit rozlišuje úplné a částečné jazyky

`config/locales.php` má u každého jazyka příznak `audit`. Ruština má `audit => false`, protože je záměrně částečná — jinak by `translations:audit` hlásil tisíce chybějících klíčů a přestal by být použitelnou bránou. Po dokončení překladu stačí příznak přepnout.

---

## 4. Pořadí stránek v menu

Navigace i patička se řadily podle `created_at`, takže pořadí určovalo, v jakém sledu seeder vkládal — **FAQ před VIP a Premium**, opačně než návrh. Z administrace to nešlo změnit vůbec.

- `pages.sort_order` + index; existující řádky očíslovány podle dosavadního pořadí, takže se při nasazení nic viditelně nepohne
- pole **Pořadí** v administraci stránek
- `Page::scopeOrdered()` používá hlavička i patička
- `PageSeeder` nastaví pořadí podle návrhu

Výsledek odpovídá Figmě: `vip-premium | faq | ethics | contact`.

---

## 5. Nové testy (12)

`tests/Feature/DatabaseTranslationsTest.php`:

- řádek v databázi přepíše hodnotu ze souboru
- vnořené klíče (`landing.advert.title`) se vloží na správné místo a nerozbijí sousedy
- klíč bez přepisu jde dál ze souboru
- přepis platí jen pro svůj jazyk
- prázdná hodnota padá zpět na soubor
- úprava se projeví v témže requestu (uložení i smazání)
- import naplní tabulku ze souborů
- import zachová ruční úpravy; `--force` je vrátí na výchozí
- `overridden()` vrací jen skutečně změněné řádky
- ruština je podporovaná, částečné překlady padají na fallback a nejsou v auditu
- stránky se řadí podle `sort_order`, ne podle data vzniku

---

## 6. Co dál

| Co | Stav |
|---|---|
| Texty webu editovatelné v administraci | ✅ všechny přes Překlady |
| Ruština | ✅ základ, zbytek doplnitelný v administraci |
| Pořadí menu dle Figmy | ✅ |
| Členské předplatné | ✅ (předchozí etapa) |
| Země, segmenty, služby, stránky, blog | ✅ |
| **Zámek hodnocení v kartě a na detailu** | zbývá — šablona; `Gate::allows('view-ratings')` je připravený |
| **Toggle switche ve filtrech** | zbývá — šablona; `x-toggle-switch` existuje |
| **Banner „platí do…"** | zbývá — šablona; `membershipEndsAt()` je připravený |
| **Přepínač 3 jazyků v mobilním menu** | zbývá — šablona; `Locales::all()` vrací i vlajky |
| **6 mrtvých odkazů `href="#"`** | zbývá — šablona |
| Mobilní verze (27 rámců Figmy) | neověřeno |
