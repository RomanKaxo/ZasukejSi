# Mobilní verze proti Figmě — první etapa

**Datum:** 15. 8. 2026
**Referenční rámce:** 31 mobilních rámců z exportu (`C:\Users\medion\Desktop\xxx`), šířky 359–360 px
**Metoda:** hodnoty z Figmy odečteny programově z 1:1 JPG exportů (detekce hran po řádcích a sloupcích), implementace změřena přes `getBoundingClientRect()` a `getComputedStyle()` při šířce 360 px

---

## 1. Co export obsahuje

Rámce se podařilo přiřadit k dvaceti obrazovkám:

| Rámec | Obrazovka |
|---|---|
| `phone 360 px-11` | Homepage (CZ) |
| `phone 360 px-16, -18, -19, -20` | Homepage (EN, značka **escort-online.com**) |
| `phone 360 px-17` | Mřížka karet |
| `phone 360 px-12, -14, -24, -25, -26` | Detail profilu |
| `phone 360 px-13` | Základní údaje poskytovatelky |
| `phone 360 px-4` | Moje služby a ceny |
| `phone 360 px-5` | Statistiky |
| `phone 360 px` | Fotografie a video |
| `phone 360 px-1, -2` | Členská nástěnka |
| `phone 360 px-3` | Nahlášené dívky |
| `phone 360 px-15` | Vyhledávací panel |
| `phone 360 px-21, -22, -23` | Výběr země a města |
| `phone 360 px-6, -7, -8` | Registrace (volba, potvrzení, formulář) |
| `phone 360 px-9` | Login |
| `phone 360 px-10` | Věková brána 18+ |
| `menu default`, `menu logged-in muž` | Mobilní menu |
| `lightbox-2, -3` | Lightbox galerie |

**Poznámka:** anglická verze má v Figmě vlastní značku **escort-online.com**, ne ZAŠUKEJSI.CZ. To je věc, kterou je potřeba potvrdit — implementace používá jedno logo pro všechny jazyky.

---

## 2. Dobrá zpráva

Mobil nebyl ignorován. Existují rozsáhlé `@media` bloky s konkrétními rozměry a **na 360 px nedochází k vodorovnému přetoku** (`scrollWidth` = 360), což je u mobilu hlavní riziko. Nalezené rozdíly jsou proto adresné, ne systémové.

---

## 3. Nalezené rozdíly a jejich oprava

### M1 — Počítadla registrovaných byla na mobilu skrytá

Nejzávažnější nález. `.search-hero-badges` mělo `display: none !important` v bloku do 425 px, takže **na mobilu zmizely obě pilulky s počty**, přestože je Figma jasně ukazuje. Ve stejném bloku přitom zůstala pravidla, která pilulkám nastavovala rozměry — stylovaly prvek, který se nevykresloval.

| | Figma | Před | Po |
|---|---|---|---|
| zobrazení | viditelné | `display:none` | viditelné |
| rozměr | 310 × 35 | — | 310 × 35 |
| pozice x | 25 | — | 25 |
| mezera mezi pilulkami | 6 | — | 6 |
| mezera k bílé kartě | 33 | — | 32 |

Ve Figmě pilulky leží **nad** bílou vyhledávací kartou na fotce pozadí, ne uvnitř karty jako na desktopu. Řešeno absolutním pozicováním vůči kartě; `.search-hero-top` musel přejít na `position: static`, aby se pilulky vztahovaly ke kartě, a ne k řádku s nadpisem.

Zároveň bylo nutné rozšířit mezeru mezi podnadpisem a kartou ze 40 na 140 px (31 + 76 + 33), jinak by pilulky překryly podnadpis. Tato změna je omezená na šířky do 425 px — mezi 426 a 640 px se pilulky nezobrazují, takže by tam jinak zůstal prázdný pruh.

### M2 — Karty profilů byly širší a posunuté

| | Figma | Před | Po |
|---|---|---|---|
| šířka karty | 146 | 160 | 146 |
| levý okraj | 23 | 13 | 24 |
| mezera mezi sloupci | ~20 | 14 | 20 |
| šířka fotky | 146 (celá karta) | 145 (na střed) | 146 |

Mřížka měla sloupce `minmax(0, 1fr)` = 154 px, ale karty byly 160 px, takže z nich vyčnívaly a končily o 10 px blíž kraji, než má design. Ve Figmě navíc fotka vyplňuje celou šířku karty, zatímco implementace ji měla o 15 px užší a vycentrovanou.

Omezeno na 426 px, aby si tablety (427–767 px) zachovaly širší kartu.

### M3 — Položky menu byly užší a rozestupy větší

| | Figma | Před | Po |
|---|---|---|---|
| šířka položky | 310 | 304 | 310 |
| pozice x | 25 | 28 | 25 |
| rozteč | ~61 | 68 | 62 |

Šířka 304 px posunula položky o 3 px dovnitř oproti tlačítkům Registrace/Log-in pod nimi, která 310 px mají. `mb-2` (8 px) dělalo rozteč 68 místo 61.

### M4 — Řádkování podnadpisu v hero

Figma má tři řádky podnadpisu přesně 28 px od sebe; implementace používala poměr 1,3 → 23,4 px. Opraveno na 28 px.

### M5 — Boční okraj textu v hero

Nadpis i podnadpis začínaly na x = 32 (16 px z `.hero-inner` + 16 px z `.hero-copy-block`), Figma má napříč celou obrazovkou okraj 25 px. Opraveno.

Řádkování nadpisu naopak sedí — Figma má mezi prvními dvěma řádky 38 px, implementace 38,88 px. Zde jsem nic neměnil.

---

## 4. Ověření

Vše měřeno při 360 px po přestavbě assetů:

- pilulky 310 × 35 na x = 25, mezery 32 / 6 / 32
- karty 146 px na x = 24 a 190, mezera 20, fotka 146 px
- položky menu 310 px na x = 25, rozteč 62
- `scrollWidth` = 360 → **žádný vodorovný přetok**

Kontrola, že se nezměnil desktop (1280 px): karty 210 px s mezerou 22, pilulky uvnitř karty (`position: static`), hero karta 1111 px, řádkování podnadpisu 33,8 px — vše jako dřív.

Kontrola šířky 500 px: pilulky skryté, mezera zpět na 40 px — žádný prázdný pruh.

**157 testů prochází**, `translations:audit` prochází.

---

## 5. Co zbývá

Tato etapa pokryla **homepage, mřížku karet a menu**. Neověřeno zůstává:

1. **Detail profilu** (5 rámců) — nejsložitější obrazovka
2. **Stránky poskytovatelky** — služby a ceny, statistiky, fotografie, základní údaje
3. **Členské stránky** — nástěnka, nahlášené dívky
4. **Modály** — registrace (3 rámce), login, věková brána, lightbox
5. **Výběr země a města** (3 rámce)
6. **Anglická verze** — jiná značka v Figmě, viz poznámka výše

Ve statistikách si všímám, že Figma kreslí graf jako **vodorovné pruhy s datem vlevo**, zatímco implementace používá Chart.js. To je potřeba ověřit zvlášť.
