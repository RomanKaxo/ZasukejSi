# Mobil — detail profilu proti Figmě

**Datum:** 16. 8. 2026
**Referenční rámec:** `phone 360 px-12.jpg` (360 × 5345), varianty `-14`, `-24`, `-25`, `-26`
**Metoda:** hodnoty z Figmy odečteny programově z 1:1 exportu (detekce hran po řádcích a sloupcích), implementace změřena přes `getBoundingClientRect()` při šířce 360 px

---

## 1. Co sedí

Horní část obrazovky odpovídá návrhu na pixel:

| Prvek | Figma | Implementace |
|---|---|---|
| Boční okraj obsahu | x = 25 | x = 25 |
| Odkazy akcí (Obnovit přístup · Dát hodnocení · Nahlásit) | x = 25, š = 310, v = 13 | x = 25, š = 310, v = 14 |
| Pruh hodnocení | v = 40 | x = 25, š = 310, v = 40 |
| Lokace | x = 25 | x = 25, š = 310 |
| Kontejner tabulky parametrů | x = 25, š = 310 | x = 25, š = 310 |
| Výška fotogalerie | 444 | 443 |

**Žádný vodorovný přetok** — `scrollWidth` = 360.

---

## 2. Dva rozdíly, které jsem záměrně neopravil

Oba jsem naměřil a u obou jsem dospěl k tomu, že oprava není změna rozměru, ale zásah do struktury. Po zkušenosti z anglické mřížky jsem je nechtěl dělat naslepo.

### 2.1 Fotogalerie nekončí u pravého okraje

| | Figma | Implementace |
|---|---|---|
| Fotka | x = 25, š = 310 | x = 25, š = 310 |
| Kontejner | 25 → 359 (š = 335) | 25 → 335 (š = 310) |

Ve Figmě od x = 335 začíná **další snímek** a běží pod pravý okraj obrazovky — mezi snímky není mezera (ověřeno hledáním světlého sloupce mezi x = 320 a 360, nic se nenašlo).

**Zkusil jsem to a vrátil zpět.** Rozšíření kontejneru na 335 px rozšíří sloupec gridu, který sdílí s pruhem hodnocení a odkazy akcí, a ty pak přetečou na 385 px. Reprodukovat ten přesah znamená vyjmout karusel z gridu — strukturální změna, ne šířka.

### 2.2 Oddělovače v tabulce parametrů

| | Figma | Implementace |
|---|---|---|
| Oddělovač | x = 25 → 334 (š = 310) | řádek x = 37, š = 286 |
| Popisek | x ≈ 45 | odsazený `padding` tabulky |

Figma má **čáru přes celou šířku a popisek odsazený**; implementace odsazuje obojí o 12 px přes `padding: 0 12px` na tabulce. Rozdělit to znamená najít, kde se čára kreslí — `border-bottom` řádku je `0px`, takže vzniká jinde — a přesunout odsazení z tabulky na popisek.

---

## 3. Co jsem nemohl ověřit

- **Zda je pruh za fotkou náhled dalšího snímku, nebo přesah jedné fotky.** Obsah je souvislý bez mezery; z exportu to nerozliším. Na chování to má vliv — náhled znamená karusel, přesah jen širší obrázek.
- **Zbytek stránky pod y = 900.** Rámec má 5 345 px; změřil jsem horní obrazovku, kde je hlavička, jméno, akce, galerie, hodnocení a začátek tabulky parametrů. Zbytek (ceník, služby, o mně, slidery) zatím ne.
- **Varianty `-14`, `-24`, `-25`, `-26`** — liší se stavem (ověřená/neověřená fotka, online, přihlášený uživatel), rozvržení jsem u nich neměřil.

---

## 4. Doporučení

Oba rozdíly z oddílu 2 jsou reálné, ale vyžadují zásah do struktury gridu. Udělal bych je jako samostatnou práci s možností ověřit výsledek na vaší dev instanci — u anglické mřížky se ukázalo, že lokální měření rozdíly mezi variantami zakrývá.

Zbývá také dokončit měření spodních dvou třetin obrazovky.
