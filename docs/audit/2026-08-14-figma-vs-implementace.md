# Figma vs. implementace — seznam rozdílů

**Datum:** 14. 8. 2026
**Podklad:** kompletní export `C:\Users\medion\Desktop\xxx` — 72 JPG v měřítku **1:1** (desktop 1920 px, mobil 360 px) + `Escort.pdf`
**Doplněno:** metadata z Figma MCP pro node `197:5610` (frame „VIP")
**Implementace:** `http://127.0.0.1:8000`, viewport 1920 × 1080
**Zásah do kódu:** žádný — dle vašeho rozhodnutí pouze analýza.

---

## 1. Metodika

Žádná hodnota níže není odhad:

| Strana | Zdroj |
|---|---|
| **Figma — geometrie** | souřadnice z `get_metadata` (frame VIP) + měření na exportu 1:1 |
| **Figma — barvy** | vzorkování pixelů z JPG přes PHP GD, průměr přes malou oblast |
| **Implementace** | `getBoundingClientRect()` + `getComputedStyle()` nad živým DOM |

**Pokryto:** `home.jpg` (1920 × 5509) a `VIP.jpg` (1920 × 4469) — dvě hlavní desktopové obrazovky.
**Nepokryto:** zbylých 70 rámců (mobil 360 px, `muž - záložky`, `lightbox`, `ne VIP`, `vybraná země`, `menu*`). Viz §6.

> Pozn.: JPEG komprese posouvá jednotlivé pixely. Barvy níže jsou průměry z ploch bez textu a ikon; kde vzorek kolidoval s obsahem, je to uvedeno.

---

## 2. Rozdíly — úvodní stránka

### ❌ H1 — Pořadí položek v hlavním menu

| | Pořadí |
|---|---|
| **Figma** | Úvod · **VIP a Premium** · **FAQ** · Etika · Kontakt |
| **Implementace** | Úvod · **FAQ** · **VIP a Premium** · Etika · Kontakt |

Menu se řadí podle `created_at` (`AppServiceProvider`), a `PageSeeder` zakládá FAQ dřív než VIP & Premium. Prohozené jsou 2. a 3. položka.

### ❌ H2 — Pořadí prvků v kartě profilu

| Pozice | Figma (hlavní mřížka) | Implementace |
|---|---|---|
| 1 | Jméno + VIP | Jméno + VIP |
| 2 | Tlačítko Detail | Tlačítko Detail |
| 3 | **Hodnocení** | **168 cm / 19 let** |
| 4 | Lokalita | Lokalita |
| 5 | **168 cm / 19 let** | **Hodnocení** |

Pozice 3 a 5 jsou prohozené.

> Figma sama není konzistentní: **druhá** řada karet na téže stránce má pořadí jako implementace. Hlavní mřížka pod „Nejlepší výsledky" má ale hodnocení hned pod tlačítkem.

### ❌ H3 — Hodnocení je v návrhu zamčené, v implementaci odhalené

| | Figma | Implementace |
|---|---|---|
| Obsah | `Hodnocení:` + **růžová ikona zámku** | `Hodnocení: 4.8/5` + srdíčko |
| Pozadí | dvoutónové **#D9D9D9** / **#E8E8E8** | jednolité **#E6FEE8** (světle zelená) |

Návrh hodnotu před nepřihlášeným návštěvníkem skrývá — proto ten zámek a proto pruh *„Premium účet vám odemkne hodnocení"*. Implementace hodnotu ukazuje všem.

### ❌ H4 — Rozmazání je aplikované obráceně

| | Fotka | Hodnocení |
|---|---|---|
| **Figma** | ostrá u všech karet | zamčené u všech karet |
| **Implementace** | **rozmazaná** u ~poloviny karet + velký zámek 72 × 72 přes obrázek | **viditelné** |

V `ProfileList` rozhoduje `crc32($profile->id) % 2`, takže se rozmaže zhruba každá druhá karta. Návrh nic takového nemá — zamyká se konzistentně hodnocení, ne fotka.

### ❌ H5 — Přepínače ve filtrech chybí

Figma má u „Verified photo", „Video" a „Porno actress" **skutečné toggle switche** (pilulka s kolečkem vlevo = vypnuto). Implementace používá běžné filtrační pilulky — v DOM není ani jeden `input[type=checkbox]` ani `role=switch`.

**V projektu přitom existuje `resources/views/components/toggle-switch.blade.php`** a používá se v `profile-form.blade.php` a `services-manager.blade.php`. Pro tuto sekci se nepoužívá.

### ❌ H6 — Výška filtračních pilulek: 41 px místo ~33 px

Změřeno na exportu při 6× zvětšení: pilulka „18-25 yo" je **80 × 33 px**.
Implementace: „18-25 let" je **96 × 41 px**.

Rozdíl šířky je zčásti dán textem (`yo` vs `let`), **výška je ale o 8 px větší**.

### ⚠️ H7 — Rozestup karet v mřížce: 23,6 px místo 21,5 px

| | Rozteč | Mezera |
|---|---|---|
| Figma | 231,5 px | **21,5 px** |
| Implementace | 233,6 px | **23,6 px** |

Mřížka má sloupce `217,59 px`, zatímco karta je 210 px — uvnitř sloupce zbývá 7,6 px vůle. Figma má sloupce přesně 210 px.

### ⚠️ H8 — Výchozí hodnoty vyhledávání

| Pole | Figma | Implementace |
|---|---|---|
| Kraj | **Hlavní město Praha** | Praha |
| Věk | **18 let** | 18-25 let |

Souvisí s nejednotností v kódu: `SearchProfiles::getAllRegionsProperty()` používá `Praha`, `MemberController::CZECH_REGIONS` používá `Hlavní město Praha`, a `cities.admin_name` obsahuje `Praha`.

### ℹ️ H9 — Řada „Segment" ve filtrech není v návrhu

Implementace vykresluje navíc řádek `Segment · Nová · Ověřená · Top lokalita`. V návrhu není — jde o pozdější funkci, ne o odchylku od Figmy.

### ℹ️ H10 — Jazyk popisků

Návrh míchá lokalizace: filtry a eco pruh jsou anglicky („All Girls", „Verified photo", „Our project is eco-friendly"), zatímco menu, hero a vyhledávání česky („zrušit filtr"). Implementace je konzistentně česká. **Nepovažuji za chybu implementace** — je to nekonzistence v návrhu.

---

## 3. Rozdíly — detail profilu (frame „VIP")

### ❌ V1 — Mezera mezi dlaždicemi galerie: 14 px místo 10 px

| | Figma | Implementace |
|---|---|---|
| Levá → střed | 1020,33 → 1030,32 | 1026,5 → 1040,5 |
| Střed → pravá | 1567,57 → 1577,57 | 1577,5 → 1591,5 |
| **Mezera** | **10 px** | **14 px** |

Rozměry dlaždic sedí přesně (337,43 / 537,25 / 337,43 × 537).

### ❌ V2 — Výška řádku ceníku: 45,5 px místo 36 px

Oddělovače `Vector 105–108` na `y = 1223 / 1259 / 1295 / 1331` → rozteč **36 px**.
`.vip-pricing-table tbody tr` má **45,5 px** → u 4 řádků **+38 px**.

Šířka tabulky sedí přesně: 526 px.

### ❌ V3 — Rozteč řádků parametrů v levém panelu: 21,6 px místo 36 px

Oddělovače `Vector 100–104` v `Group 397` → rozteč **36 px**.
`.vip-profile-meta-row` má **21,6 px** → u 5 řádků **−72 px**.

### ⚠️ V4 — Šířka obsahu levého panelu: 237 px místo 231,47 px

Vnější panel sedí (261 px), vnitřní blok je o **5,53 px** širší.

### ⚠️ V5 — Video blok: 242 px místo 254,24 px

Figma `Group 401` = 254,24 × 460,64; `.vip-video-card` = 242 × 460. Výška sedí, šířka je o **12,24 px** menší.

### ⚠️ V6 — Šířka textu „o mně": 820 px místo 844,1 px

### ⚠️ V7 — Jedna velikost nadpisů místo dvou

| Nadpis | Figma (box) | Odvozeno | Implementace |
|---|---|---|---|
| „Více o mně", „Video", „Moje ceny", „Služby" | 137,57 × 28,98 | **~24 px** | 34 px |
| „Nejlépe hodnocené dívky tento měsíc" | 701,73 × 60,76 | ~34 px | 34 px |

`.vip-section-title` (34 px / 800 / letter-spacing −1,36 px) se používá na obojí. Odvozeno z rozměru textového boxu — potvrdit lze jen přes Dev Mode.

---

## 4. Ověřené shody

Tyto prvky odpovídají návrhu přesně — **neměnit**:

| Prvek | Figma | Implementace |
|---|---|---|
| Karta profilu | 210 × 520,65 | 210 × 520 ✅ |
| Obrázek karty | 210 × 265 | 210 × 265 ✅ |
| Tlačítko Detail | 170 × 45 | 170 × 45 ✅ |
| Statistická pilulka | 82 × 30 | 82 × 30 ✅ |
| Odznak VIP | 50 × 26 | 50 × 26 ✅ |
| Odznak „VIP PROFIL" | 93 × 30 | 93 × 30 ✅ |
| InCall / OutCall | 113 × 40 | 113 × 40 ✅ |
| Galerie L / střed / P | 337,4 / 537,3 / 337,4 × 537 | 337 / 537 / 337 × 537 ✅ |
| Šířka ceníkové tabulky | 526 | 526 ✅ |
| Levý panel — šířka | 261 | 261 ✅ |
| Eco pruh | 1136 × 35 | 1136 × 35 ✅ |

### Barvy — všechny sedí

| Role | Figma (vzorkováno) | Implementace |
|---|---|---|
| Primární růžová | **#DD3789** | #DD3888 ✅ |
| Sekundární fialová | **#5C2D63** | #5C2D62 ✅ |
| VIP zlatá | **#FFB700** | #FFB700 ✅ |
| Odznak OVĚŘENO | **#CDEECF** | #CDEFD0 ✅ |
| Statistická pilulka | **#F2F2F2** | #F2F2F2 ✅ |
| Pozadí stránky / karty | #FFFFFF | #FFFFFF ✅ |

Odchylky 1–2 jednotky jsou artefakt JPEG komprese, ne rozdíl.

---

## 5. Placeholder data v návrhu

Frame obsahuje `168 cm`, `19 let`, `4,9/5`, `4,8/5`, `4,7/5`, jména `Alexandra / Tamara / Jana / Angela / Lucie`, ceník `4 000 / 8 000 / 14 000 / 18 000 Kč` a počty `1420 dívek / 382 mužů`.

Jsou to **ukázková data návrháře, ne specifikace**. Kód je navíc opisoval nepřesně — měl `6 000 Kč` tam, kde návrh má `8 000 Kč`.

**Dohodnutý postup:** tyto hodnoty patří do **seed dat v databázi**, ne zpět do šablon jako fallbacky. Stránka pak bude vypadat jako návrh a přitom si nic nevymýšlí. *(Zatím neprovedeno — do kódu se nezasahovalo.)*

---

## 6. Co zbývá zkontrolovat

Auditovány 2 ze 72 rámců. Zbývá:

| Skupina | Souborů | Obsah |
|---|---|---|
| `phone 360 px*` | 27 | kompletní mobilní verze |
| `muž - záložky*` | 5 | členská sekce (oblíbené, hodnocení, archiv, nahlášené) |
| `home-1` … `home-25` | 25 | stavy a varianty úvodní stránky |
| `VIP-1..3`, `ne VIP` | 4 | varianty detailu profilu |
| `lightbox*` | 4 | galerie na celou obrazovku |
| `vybraná země`, `vybrané město v zemi*` | 3 | stránka zemí |
| `menu default`, `menu logged-in muž` | 2 | mobilní menu |

Mobilní verze (360 px) je zatím zcela neověřená — a je jí 27 rámců, tedy víc než desktopu.

---

## 7. Poznámky k měření

- Viewport 1920 px má ~15 px scrollbar → využitelných **1905 px**. Absolutní `x` jsou proto proti Figmě posunuté cca o 7,5 px; **rozdíly v `x` samy o sobě nehlásím**, jen rozměry a mezery.
- Detail profilu měřen na profilu `21` (má vyplněný ceník). Profil `3` ceník nemá → prázdný stav.
- Oba slidery doporučených profilů vykreslují 0 karet místo 5+5, protože tento měsíc nikdo nehodnotil (důsledek opravy F9). Vizuálně největší odchylka od návrhu, ale jde o **data, ne layout** — vyřeší seed dle §5.
