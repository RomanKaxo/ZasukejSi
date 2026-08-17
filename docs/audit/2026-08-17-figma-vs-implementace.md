# Frontend proti Figmě

**Datum:** 17. 8. 2026
**Zdroj:** Figma soubor *Escort* (`qx3Prju6VpnFHiXeiwjo7r`), stránka **main design**
**Metoda:** 72 rámců vyexportováno 1:1 z desktopové aplikace, hodnoty odečteny programově detekcí hran; implementace změřena přes `getBoundingClientRect()` při šířce 1920

---

## 0. Jak jsem se k datům dostal

Dev Mode MCP server nešel použít: účet `dev.stanektech@gmail.com` má seat **View**, a ten Dev Mode nedostává — v Preferences desktopové aplikace položka pro MCP server vůbec není. Vzdálený Figma MCP má pro View seat strop **dvě volání**, který se vyčerpal na výpisu stránek.

Cestou ven byl export přímo z aplikace: 72 rámců, 48 MB, ve složce `figma-export/`. Měření pak proběhlo lokálně nad obrázky, takže je opakovatelné a nezávislé na přístupu do Figmy.

**Přesnost:** hodnoty odečtené z 1:1 exportu mají toleranci ±1 px kvůli JPEG hranám. Kde je rozdíl v jednotkách pixelů, uvádím to jako „ověřit v Dev Mode".

Soubor má tři stránky: **Cover**, **main design**, **prez**. Auditoval jsem *main design*.

---

## 1. Nález, který je vidět na první pohled

### 1.1 Navigační lišta je na širokých displejích useknutá a odsazená doleva

`<nav>` má `position: fixed; left: 0; right: 0` **a zároveň `max-width: 1280px`**. Fixovaný prvek s levou i pravou nulou se tím ořízne na 1280 px ukotvených vlevo, a `mx-auto` na vnitřním kontejneru už nemá co centrovat.

Naměřeno při šířce 1920:

| | Figma | Implementace | Rozdíl |
|---|---|---|---|
| Logo (levý okraj) | x = 394 | x = 72 | **−322 px** |
| Menu „Úvod" | x = 681 | x = 377 | **−304 px** |
| Pravý okraj lišty | ≈ 1520 | 1208 | **−312 px** |
| Karty profilů | 390 → 1530 | 385 → 1521 | v pořádku |

Celá hlavička tedy sedí v levých dvou třetinách obrazovky, zatímco tělo stránky je vycentrované. **Do šířky 1280 px se chyba neprojeví** — proto jsem ji v dřívějších měřeních (1280) nikdy neviděl.

Oprava je na jednom místě: `max-width` patří na vnitřní kontejner, ne na samotný `<nav>`.

### 1.2 Patička není zarovnaná s obsahem

Řádek patičky začíná na x = 401, karty na x = 385. Šestnáct pixelů, ale je to systematický posun celého bloku. Ve Figmě obojí začíná na x ≈ 390.

---

## 2. Karty profilů na homepage

Změřeno detekcí svislých hran fotek v rámci `home.jpg` (1920 × 5509), pět sloupců:

| | Figma | Implementace |
|---|---|---|
| Levý okraj mřížky | 390 | 385 |
| Pravý okraj mřížky | 1530 | 1521 |
| Šířka mřížky | 1140 | 1136 |
| Rozteč sloupců | 231,5 | 231,5 |
| **Šířka karty** | **≈ 214** | **210** |
| Mezera mezi kartami | ≈ 17,5 | 21,5 |

Rozteč sedí přesně, takže mřížka jako celek odpovídá. Liší se rozdělení uvnitř rozteče: návrh má **širší kartu a užší mezeru**, implementace naopak. Rozdíl na kartu jsou čtyři pixely.

Svisle vychází výška fotky v návrhu na **≈ 240 px** proti 265 px v implementaci. Tenhle rozdíl je větší než tolerance metody, ale zaokrouhlení rohů fotky mi znepřesňuje horní hranu — **doporučuji potvrdit v Dev Mode**, kde je hodnota čitelná přímo.

---

## 3. Slidery „Nejlépe hodnocené dívky" — rozpor mezi zadáním a návrhem

Tohle je nejdůležitější zjištění celého auditu.

V obou variantách detailu profilu (`VIP.jpg` i `ne VIP.jpg`) jsou v obou sliderech **rozostřené všechny karty bez výjimky** a **každá z nich nese odznak „★ VIP"**.

Co je v návrhu rozostřené a co ne:

| Prvek | Stav v návrhu |
|---|---|
| Fotka | rozostřená + visací zámek |
| Jméno | rozostřené |
| Odznak VIP | **ostrý** |
| Tlačítko „Detail" | **ostré a čitelné** |
| Výška, věk, lokalita | **ostré** |
| Hodnocení (4,9/5) | **ostré** |

Implementace rozostřuje **fotku a jméno**, ostatní nechává ostré — to odpovídá. Tlačítko Detail je od minulé opravy prokliknutelné — také odpovídá.

**Neodpovídá jediná věc: které karty se rozostřují.** Vy jste zadal, že skryté mají být jen VIP profily, a tak to je implementované. Návrh rozostřuje všechny — ale zároveň v něm mají všech pět karet VIP odznak.

Z toho plynou dva možné výklady a **nedokážu mezi nimi rozhodnout za vás**:

1. **Slider má obsahovat jen VIP dívky.** Pak návrh a vaše zadání říkají totéž a chyba je jinde — v tom, že implementace do slideru pouští i profily bez VIP.
2. **Slider obsahuje kohokoliv a rozostření je paušální**, protože je vázané na Premium účet diváka, ne na VIP inzerentky. Pak je současné chování proti návrhu.

Popisek u slideru zní „Premium účet vám odemkne hodnocení", což **mluví pro druhý výklad** — bránou je Premium diváka, ne VIP profilu.

---

## 4. Patička

Návrh (potvrzeno z `home.jpg`):

| Sloupec 1 | Sloupec 2 | Sloupec 3 |
|---|---|---|
| Časté dotazy | Ochrana osobních údajů | VIP účet pro dívky |
| Kontakt | Etika a bezpečnost | Prémium účet pro pány |

Tedy přesně to, co jste posílal na snímku. Odpovídá i tlačítko Registrace, box o diskrétnosti vpravo, ekologický štítek, oddělovač a copyright.

Rozdíly proti implementaci:

| Věc | Návrh | Stav |
|---|---|---|
| „Obchodní podmínky" | není | **ponecháno na vaše rozhodnutí** |
| Obecný odkaz „VIP a Premium" | není | zobrazuje se jen nepřihlášeným |
| „Časté dotazy" vs „FAQ" | Časté dotazy | název stránky je „FAQ" — přejmenujte v administraci |

Dvě cílené položky pro dívky a pány, které jste si vyžádal, návrhu odpovídají — a teď už chápu proč: návrh je kreslený pro nepřihlášeného návštěvníka, kterému dává smysl vidět obě.

---

## 5. Nesrovnalosti přímo uvnitř Figmy

### 5.1 Překlep v popisku slideru

V obou variantách detailu stojí **„Premuim účet vám odemkne hodnocení"** — prohozená písmena, a navíc dvojitá mezera mezi „vám" a „odemkne".

Implementace má správně „Premium účet vám odemkne hodnocení". **Je to vědomá odchylka a nechal bych ji tak** — jen ať víte, že tam není omylem.

### 5.2 Rámce „phone 360 px" nejsou 360 px

Šest z dvaceti sedmi mobilních rámců je široké **359 px**, ne 360:

`phone 360 px.jpg`, `-1`, `-2`, `-3`, `-4`, `-5`

Zbylých dvacet jedna má 360. Rozdíl je jeden pixel, takže na měření nemá vliv, ale znamená to, že těch šest rámců vzniklo jinak než ostatní — a při odečítání odsazení z nich vychází hodnoty o půl pixelu jinde.

### 5.3 Dvacet pět variant „home"

Rámce `home` až `home-25` mají výšky od 893 do 5509 px. Jsou to zjevně stavy téže obrazovky (filtry, přihlášený/nepřihlášený, prázdné výsledky), ale **nejsou nijak pojmenované** — z názvu nepoznáte, který stav zachycují. Totéž `muž - záložky` (5 variant) a `VIP-1/2/3` (tři rámce shodných rozměrů 1920 × 683).

Na implementaci to vliv nemá, ale je to důvod, proč nemůžu odpovědně říct „všechny stavy sedí" — nevím, které stavy to jsou.

---

## 6. Co odpovídá

- Menu: Úvod · VIP a Premium · FAQ · Etika · Kontakt — pořadí i názvy sedí
- Vlajka jazyka: kruhová vlajka v šedém zaobleném čtverci; implementace má 58 × 58 se zaoblením 8 px a vlajku 26 × 26 — **sedí** (tohle byla oprava z minulé dávky)
- Rozteč mřížky karet 231,5 px
- Rozostření v detailu profilu se týká fotky a jména, ne hodnocení a tlačítek
- Struktura patičky: tři sloupce po dvou odkazech, tlačítko vlevo, bezpečnostní box vpravo

---

## 7. Co jsem neověřil

**Neprošel jsem všech 72 rámců.** Ověřil jsem homepage (desktop), detail profilu v obou variantách, patičku a navigaci. Neověřeno zůstává:

- účet slečny a účet pána (`muž - záložky` × 5)
- výběr země a města (`vybraná země`, `vybrané město v zemi` × 2)
- lightbox galerie (4 varianty)
- mobilní rámce nad rámec detailu profilu, který jsem měřil dřív
- stránka **prez** — vůbec jsem ji neotevřel

**Barvy, fonty a odsazení jsem neměřil systematicky.** Z JPEG exportu jde odečíst geometrie, ne hodnoty proměnných. Na to je potřeba Dev Mode, tedy placený seat.

---

## 8. Co bych řešil v tomto pořadí

1. **Navigační lišta na širokých displejích** (§1.1) — jediná chyba, která je na 1920 px vidět na první pohled. Oprava je jednořádková.
2. **Rozhodnout spor u sliderů** (§3) — potřebuji od vás, který z těch dvou výkladů platí.
3. Zarovnání patičky s obsahem (§1.2).
4. Šířka karty 210 → 214 (§2), po potvrzení v Dev Mode i výška fotky.
5. Přejmenovat stránku FAQ na „Časté dotazy" (§4) — to je změna v administraci, ne v kódu.
