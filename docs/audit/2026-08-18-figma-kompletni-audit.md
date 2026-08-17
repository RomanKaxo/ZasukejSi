# Kompletní audit frontendu proti Figmě

**Datum:** 18. 8. 2026
**Zdroj:** Figma soubor `Xc5RwCdgdZhbzU3MLPm6zL` („Untitled"), stránka **Page 1**
**Rozsah:** všech 72 rámců
**Nahrazuje:** [audit ze 17. 8.](2026-08-17-figma-vs-implementace.md), který pokrýval jen homepage, detail profilu, patičku a navigaci

> **Stav oprav k 18. 8. večer.** Vyřešeno: §2.1 věková brána (nově i s vypínačem a editací v administraci), §2.5 „Recenze", §6.2 zarovnání patičky, §6.3 šířka karty, §2.4 mobilní menu, přejmenování FAQ. Podrobnosti u jednotlivých nálezů.
>
> §6.1 (navigační lišta) se ukázal jako **neaktuální** — měření při 1920 px ukázalo lištu správně vycentrovanou. Skutečná odchylka byla jinde: celý obsahový blok měl 1136 px místo 1140. Viz §6.3.

---

## 0. Metoda a co bylo potřeba vyřešit

### Soubor je duplikát toho, který už mám vyexportovaný

Odkaz vede na jiný klíč souboru než minule (`qx3Prju6VpnFHiXeiwjo7r` → `Xc5RwCdgdZhbzU3MLPm6zL`). Ověřoval jsem, jestli jde o nový návrh, nebo kopii — protože na tom stojí, jestli je předchozí práce k něčemu.

Je to **kopie**. Panel vrstev v novém souboru obsahuje přesně tytéž názvy rámců, jaké mám ve `figma-export/`: `home`, `phone 360 px`, `VIP`, `muž - záložky`, `vybraná země`. Rozdíl je jen ve struktuře souboru — původní měl tři stránky (*Cover*, *main design*, *prez*), tenhle má jednu (*Page 1*) a vrstvy jsou v ní zploštělé (`Group 536`, `Frame 1115`, `image 53`).

**Důsledek:** stránka **prez** v tomhle souboru není. Pokud na ní něco záviselo, je to mimo tento audit.

### Přístup do Figmy je limitovaný

| Cesta | Výsledek |
|---|---|
| Figma MCP (vzdálené) | účet `dev.stanektech@gmail.com` je na plánu **Starter**, seat **View** → strop na několik volání, vyčerpán na výpisu stránek a jednom náhledu |
| `get_metadata` na stránku | selhává i v rámci limitu — odpověď je nad 119 kB a přenos ji utne uprostřed JSONu |
| Dev Mode | View seat ho nedostává, takže žádné odečty proměnných, barev a fontů |
| Prohlížeč | odkaz nese sdílecí token, soubor je čitelný; váš Chrome je navíc přihlášený s právy k úpravám — **nic jsem v návrhu neměnil**, jen prohlížel |

**Měřeno tedy bylo lokálně nad exportem 1:1** (72 rámců, 48 MB). Geometrie ano, hodnoty proměnných ne. Kde uvádím pixel, má toleranci ±1 px kvůli JPEG hranám.

---

## 1. Mapa všech 72 rámců

Tohle v minulém auditu chybělo a byl to důvod, proč jsem nemohl říct „všechny stavy sedí". Rámce nejsou pojmenované podle obsahu, takže jsem je musel identifikovat pohledem.

**Zásadní oprava proti minulému auditu:** napsal jsem tehdy, že `home` až `home-25` jsou varianty jedné obrazovky. Nejsou. Je to **26 různých obrazovek**, které jen sdílejí prefix názvu.

### Desktop 1920 px — ZAŠUKEJSI.CZ (33 rámců)

| Rámec | Výška | Obrazovka | Route |
|---|---|---|---|
| `home` | 5509 | Homepage, nepřihlášený | `/` |
| `home-1` | 2028 | Výpis „Hlavní město Praha", nepřihlášený | `/` |
| `home-21` | 1284 | Totéž, horní část | `/` |
| `home-25` | 1556 | Hero + rozbalený výběr kraje | `/` |
| `home-2` | 4729 | Účet dívky — Základní údaje + statistiky | `/account` |
| `home-3` | 893 | Totéž, červený alert „Dokončete registraci" | `/account` |
| `home-4` | 893 | Totéž, zelený alert „Změna údajů uložena" | `/account` |
| `home-10` | 1628 | Účet dívky — Základní nastavení, Premium alert | `/account/profile` |
| `home-8`, `home-9` | 1628 | Účet pána — Základní nastavení | `/account/member` |
| `home-11` | 2697 | Fotografie a video | `/account/photos` |
| `home-12` | 1163 | Totéž + rozbalené menu účtu | `/account/photos` |
| `home-13` | 1091 | Totéž, stav „Probíhá ověření (obvykle 72 h)" | `/account/photos` |
| `home-14` | 1152 | Totéž, stav „Máte ověřený profil" | `/account/photos` |
| `home-15` | 1029 | Modál „Nahrát fotografie", prázdný | — |
| `home-16` | 1029 | Modál „Nahrát fotografie", s fotkami | — |
| `home-17`, `home-20` | 2307 | Moje služby a ceny | `/account/services` |
| `home-18` | 2058 | Statistiky, sloupce se zvýrazněním VIP | `/account/statistics` |
| `home-19` | 1890 | Statistiky bez zvýraznění | `/account/statistics` |
| `home-5`, `-6`, `-7` | 1577–1600 | Účet pána — „Vítejte, Hercules94", tři stavy alertu | `/account/member` |
| `home-22` | 996 | Modál Registrace (Jsem žena / Jsem muž) | — |
| `home-23` | 996 | Modál Login | — |
| `VIP` | 4469 | Detail profilu, VIP | `/profil/{slug}` |
| `ne VIP` | 4143 | Detail profilu, ne-VIP | `/profil/{slug}` |
| `VIP-1`, `-2`, `-3` | 683 | Výřezy hlavičky detailu (Alexandrina / Jana / Alexandrina) | — |
| `lightbox`, `lightbox-1` | 1245 | Lightbox galerie | — |
| `muž - záložky` | 3057 | Moje favoritky | `/account/member/favorites` |
| `muž - záložky-1` | 3442 | Archiv dívek | `/account/member/archive` |
| `muž - záložky-2` | 3453 | Archiv + „TOP 50 v ČR – All time" | `/account/member/archive` |
| `muž - záložky-3` | 3159 | Nahlášené dívky | `/account/member/reported` |
| `muž - záložky-4` | 2395 | Dívky měsíce TOP 10 | `/account/member/girls-of-month` |

### Desktop 1920 px — ESCORT-ONLINE.COM (3 rámce)

| Rámec | Výška | Obrazovka |
|---|---|---|
| `home-24` | 2639 | Homepage druhé značky |
| `vybraná země` | 1451 | Výběr země (Bosna a Hercegovina) |
| `vybrané město v zemi` | 2639 | Výběr města (Jahorina) |
| `vybrané město v zemi-1` | 1482 | Hybrid: český postranní panel zemí + karty |

### Mobil 360 px (31 rámců)

| Rámec | Výška | Obrazovka | Značka |
|---|---|---|---|
| `phone 360 px` | 4125 | Fotografie a video | ZAŠUKEJSI |
| `-1` | 2895 | Účet pána + Premium alert | ZAŠUKEJSI |
| `-2` | 2895 | Účet pána + překryv služeb | ZAŠUKEJSI |
| `-3` | 1366 | Nahlášené dívky | ZAŠUKEJSI |
| `-4` | 3336 | Moje služby a ceny | ZAŠUKEJSI |
| `-5` | 2992 | Statistiky | ZAŠUKEJSI |
| `-6` | 730 | Modál Registrace | ZAŠUKEJSI |
| `-7` | 730 | „Registrace proběhla úspěšně" | ZAŠUKEJSI |
| `-8` | 1087 | Formulář „Registrace pro ženy" | ZAŠUKEJSI |
| `-9` | 1087 | Login | ZAŠUKEJSI |
| `-10` | 1087 | **Věková brána 18+** | ZAŠUKEJSI |
| `-11` | 8266 | Homepage | ZAŠUKEJSI |
| `-12` | 5345 | Detail profilu, FOTO OVĚŘENO | ZAŠUKEJSI |
| `-14` | 6025 | Detail profilu, FOTO NEOVĚŘENO (Eva) | ZAŠUKEJSI |
| `-13` | 6155 | Účet — Základní údaje | ZAŠUKEJSI |
| `-15` | 2210 | Homepage — „Nejlepší výsledky" + filtry | ZAŠUKEJSI |
| `-24`, `-25`, `-26` | 1482 | Detail profilu — tři stavy akční lišty | ZAŠUKEJSI |
| `-16` | 2237 | Homepage | ESCORT-ONLINE |
| `-17` | 1052 | Mřížka karet | ESCORT-ONLINE |
| `-18`, `-19` | 1905 | Girls in Europe, výběr země | ESCORT-ONLINE |
| `-20` | 1905 | Vybraná země + město | ESCORT-ONLINE |
| `-21`, `-22`, `-23` | 834 | Modály výběru země / města | ESCORT-ONLINE |
| `lightbox-2`, `-3` | 880 | Lightbox | ZAŠUKEJSI |
| `menu default` | 1196 | Mobilní menu, nepřihlášený | ZAŠUKEJSI |
| `menu logged-in muž` | 1196 | Mobilní menu, přihlášený | ZAŠUKEJSI |

---

## 2. Nálezy — implementace neodpovídá návrhu

### 2.1 Věková brána 18+ vůbec neexistuje ⚠️

Rámec `phone 360 px-10` je celoobrazovkový modál: velké „18", nadpis **Vítejte**, právní text a tlačítko **Vstoupit**. Blokuje vstup na web.

V kódu **není nic** — žádná komponenta, žádný middleware, žádný překladový klíč. Grepoval jsem `Vstoupit`, `age-gate`, `nulové tolerance`: nula výskytů.

U webu tohoto typu to není kosmetika. Je to jediná obrazovka z celého návrhu, která **chybí úplně**.

### 2.2 Právní text v návrhu odkazuje na jinou společnost

Text věkové brány zní: *„Společnost Euro Girls Escort uplatňuje politiku nulové tolerance…"* — přitom hlavička téhož rámce je ZAŠUKEJSI.CZ.

Je to chyba v návrhu (viz §4), ale zmiňuji ji tady, protože **kdyby se brána doplnila opisem z Figmy, dostane se do produkce cizí obchodní jméno**.

### 2.3 Výplňový text z návrhu se dostal do ostrých textů ⚠️

Návrh používá na řadě míst nesmyslnou českou výplň typu *„Oprávněné aniž i odstoupil o snadno osoby vede grafikou osobami úmyslu 60 % …"*. To je v návrhu v pořádku — je to placeholder.

**V `lang/cs/front.php` je jich jedenáct** a nejsou označené jako dočasné:

| Řádek | Klíč | Kde se zobrazí |
|---|---|---|
| 461 | `register.consent` | **souhlas při registraci** |
| 711 | `photos.verify_modal_terms` | souhlas při odeslání fotky k ověření |
| 57–59 | `hand_heart`, `icecream`, `laptop` | dlaždice na homepage |
| 139 | `premium_description` | popis Premium |
| 167 | `reported_description` | Nahlášené dívky |
| 465 | `register.success_message` | potvrzení registrace |
| 705, 708 | `photos.verify_desc`, `verify_modal_desc_1` | ověření fotek |
| 725 | `photos.verified_desc` | ověřený profil |
| 735 | `photos.video_verify_desc` | ověření videa |

Nejvážnější jsou dva souhlasy. **Souhlas s podmínkami, který je psaný nesmyslem, není souhlas.**

### 2.4 Mobilní menu přihlášeného je jiné než v návrhu

`menu logged-in muž` má **čtyři** položky a odhlášení:

| # | Návrh | Implementace (muž) |
|---|---|---|
| 1 | Můj profil | Základní nastavení |
| 2 | Moje zprávy `654` | Moje zprávy |
| 3 | Moje favoritky | Notifikace |
| 4 | Základní nastavení | Moje favoritky |
| 5 | Log-out | Hodnocení dívek |
| 6–8 | — | Dívky měsíce, Archiv dívek, Nahlášené dívky |
| 9 | — | Log-out |
| jazyky | **nejsou** | přepínač tří jazyků |

Implementace má osm položek proti čtyřem, v jiném pořadí, a navíc přepínač jazyků, který návrh přihlášenému nezobrazuje (nepřihlášenému v `menu default` ano).

**Tohle je rozhodnutí pro vás.** Návrh je buď zkrácený, nebo záměrný — jenže kdybych ho vzal doslova, Archiv dívek, Dívky měsíce a Nahlášené dívky nebudou na mobilu dosažitelné vůbec, protože jinou cestu k nim mobil nemá.

### 2.5 „Recenze" — popisek a chování si odporují

V návrhu (`home-11`) je položka **„Recenze – již brzy"** vyšedlá a neklikatelná.

U nás je to živý odkaz na `/account/reviews`, který funguje, ale **popisek zůstal „Recenze - již brzy"** (`lang/cs/front.php:133`). Uživatel vidí „již brzy" a ono to jde otevřít.

Buď popisek na „Recenze", nebo položku vypnout. Teď je to obojí zároveň.

### 2.6 Postranní panel účtu dívky má položku navíc

| # | Návrh | Implementace |
|---|---|---|
| 1 | Základní údaje | ✓ |
| 2 | Fotografie a video | ✓ |
| 3 | Moje služby a ceny | ✓ |
| 4 | Statistiky | ✓ |
| 5 | — | **Předplatné** |
| 6 | Recenze – již brzy | Recenze - již brzy |

Návrh vede na předplatné zlatou kartou **„Vydělávej víc / Aktivovat VIP"** pod menu, ne položkou v menu. Kartu máme taky — takže je cesta zdvojená. Drobnost, ale je to prvek navíc proti Figmě.

### 2.7 Tři různé lhůty ověření fotky

Jedna věc, tři čísla:

| Zdroj | Text |
|---|---|
| Figma `home-13` | „Probíhá ověření (obvykle **72 h**)" |
| `front.php:714` | „ověříme a dáme vám vědět do **72 hodin**" |
| `front.php:727` | „Obvykle to trvá **24-48 hodin**" |

Dvě z těch hlášek vidí tentýž uživatel po sobě.

### 2.8 Popisek akce v detailu profilu

Návrh: **„Nahlásit"**. Implementace: **„Nahlásit profil"** (`front.php:452`). Kosmetika, ale v mobilní liště `-25` jsou tři akce vedle sebe v úzkém sloupci a delší text mění zalomení.

---

## 3. Druhá značka — ESCORT-ONLINE.COM

Sedm rámců (`home-24`, `vybraná země`, `vybrané město v zemi`, `phone 360 px-16` až `-23`) nemá hlavičku ZAŠUKEJSI.CZ, ale **ESCORT-ONLINE.COM**, s anglickými texty:

> „We are a community of people who enjoy sex. Girls, sign up today and gain new clients."

Jsou to obrazovky výběru země a města — „Girls in Europe", seznam zemí s vlajkami a počty, výběr města.

**V kódu se řetězec `ESCORT-ONLINE` nevyskytuje ani jednou.** Funkčně ty obrazovky máme (`english-country-sidebar`, `SearchProfiles::englishCountriesData()`, `/countries`), ale běží pod značkou ZAŠUKEJSI.CZ.

**Potřebuji od vás rozhodnutí:** je escort-online.com druhá značka na vlastní doméně, nebo zbytek šablony, ze které návrh vznikl? Na odpovědi visí, jestli jsou tyhle obrazovky hotové, nebo mají mít vlastní branding.

---

## 4. Nesrovnalosti přímo uvnitř Figmy

Věci, které nejsou chybou implementace — jsou chybou návrhu. **Žádnou z nich bych nepřepisoval do kódu.**

### 4.1 Převod výšky na stopy je špatně

Detail profilu ve všech variantách uvádí **„168 cm / 5'9''"**. 168 cm je 5'6", ne 5'9". Rozdíl je 7,6 cm.

Váha vedle toho sedí (57 kg / 126 lbs ✓), takže je chybný jen tenhle jeden převod.

Implementace počítá správně (`Profile::height_feet`, `round(168/2.54)=66` → `5'6"`). **Nechte to být — kód má pravdu, návrh ne.**

### 4.2 Seznam zemí se třikrát opakuje

V modálech výběru země (`phone 360 px-22`, `-23`) jde seznam takto:

```
Albánie 484 · Andorra 45 · Arménie 24 · Belgie 484 · Bělorusko 45 ·
Bulharsko 24 · Černá Hora 4 · Česká republika 45 ·
Albánie 24 · Andorra 484 · Arménie 45 · Belgie 24 · Bělorusko 484 …
```

Tytéž země znovu, pokaždé s jiným počtem. Je to designérská výplň, ne datový model.

Původní implementace to **doslova opsala** — `english-country-sidebar.blade.php` měl 24 položek, tj. 8 zemí třikrát. Dnes je panel řízený z `CountryStatsService` a každá země je právě jednou. **To je správně a je to vědomá odchylka od návrhu.**

### 4.3 Anglické popisky filtrů na české stránce

`phone 360 px-15` má nadpis **„Nejlepší výsledky"** česky a hned pod ním filtry anglicky: *All Girls, 18-25 yo, 26-30 yo, 31-35 yo, 36-40 yo, 40-50 yo, 50 yo +, Recommendation, Verified photo, Video, Porno actress, New, Rating*.

Máme je přeložené („Všechny dívky", „18-25 let", „Doporučení", „Ověřená fotka"). **Vědomá odchylka, správná.**

### 4.4 Sloupce grafu mají všechny stejný popisek

Mobilní statistiky (`phone 360 px-5`) mají u **každého** sloupce číslo **38**, přestože sloupce mají viditelně různé délky. Popisky jsou placeholder.

### 4.5 Seznam služeb obsahuje duplicity

`phone 360 px-4` („Moje služby a ceny") vypisuje: Pozice 69, Lízání, Vaginální sex, Nadávání, Výstřik na obličej, Erotická masáž, Výstřik do pusy, Facesitting, Výstřik na tělo, Prstění — a pak **Lízání, Pozice 69, Nadávání, Vaginální sex, Erotická masáž, Výstřik na obličej znovu**.

Zmiňuji to, protože jste u scraperu výslovně žádal „nechceme žádné duplicitní názvy". Náš číselník duplicity nemá; návrh ano.

### 4.6 Věková brána mluví za Euro Girls Escort

Viz §2.2.

### 4.7 Překlep „Premuim"

V obou variantách detailu stojí **„Premuim účet vám odemkne hodnocení"** — prohozená písmena a dvojitá mezera. Implementace má správně. *(Nález z minulého auditu, potvrzen.)*

### 4.8 Šest mobilních rámců není 360 px

`phone 360 px.jpg`, `-1` až `-5` jsou široké **359 px**, zbylých 25 má 360. Jeden pixel, ale znamená to, že těch šest vzniklo jinak než ostatní. *(Nález z minulého auditu, potvrzen.)*

### 4.9 Rámce nejsou pojmenované podle obsahu

26 rámců `home*`, které jsou 26 různých obrazovek. 5× `muž - záložky`. 27× `phone 360 px`. 3× `VIP-*` shodných rozměrů.

Mapu v §1 jsem musel sestavit pohledem. **Doporučuji rámce přejmenovat** — u dalšího auditu to ušetří práci a odstraní riziko, že se dvě obrazovky zamění.

---

## 5. Co odpovídá

Ověřeno rámec po rámci:

| Oblast | Stav |
|---|---|
| Postranní panel účtu pána — 6 položek, pořadí i názvy | ✓ přesně |
| Postranní panel účtu dívky — 4 hlavní položky | ✓ (viz §2.6) |
| Mobilní menu nepřihlášeného — 5 položek + Registrace + Log-in + tři jazyky | ✓ |
| Modál Registrace — Jsem žena / Jsem muž | ✓ |
| Formulář registrace — uživatelské jméno, e-mail, heslo, potvrzení | ✓ |
| Modál Login + „Zapomenuté heslo" + „Registrovat se ZDARMA" | ✓ |
| Obrazovka „Registrace proběhla úspěšně" | ✓ |
| Lightbox galerie (desktop i mobil) | ✓ existuje |
| Ověření fotek — tři stavy (výzva / probíhá / ověřeno) | ✓ |
| Modál nahrání fotek — prázdný i naplněný | ✓ |
| Odznaky FOTO OVĚŘENO / FOTO NEOVĚŘENO | ✓ |
| Detail profilu — tři stavy akční lišty | ✓ |
| Převod výšky a váhy | ✓ (správněji než návrh) |
| Menu v hlavičce: Úvod · VIP a Premium · FAQ · Etika · Kontakt | ✓ |
| Patička: tři sloupce po dvou odkazech, Registrace, box o diskrétnosti | ✓ |
| Rozteč mřížky karet 231,5 px | ✓ |
| Počty registrovaných v hero (dřív natvrdo 1 420 / 382) | ✓ z databáze |

---

## 6. Nálezy z minulého auditu, které stále platí

Nic z toho se od 17. 8. nezměnilo:

1. **Navigační lišta na širokých displejích** — `<nav>` má `position: fixed; left:0; right:0` a zároveň `max-width: 1280px`, takže se na 1920 px ořízne a přisaje vlevo. Logo je na x = 72 místo x = 394. Do 1280 px se chyba neprojeví.
2. **Patička není zarovnaná s obsahem** — začíná na x = 401, karty na x = 385.
3. **Šířka karty** 210 px proti ≈ 214 px v návrhu.
4. **Spor u sliderů „Nejlépe hodnocené dívky"** — návrh rozostřuje *všechny* karty a všem dává VIP odznak; vaše zadání říká „jen VIP". Popisek „Premium účet vám odemkne hodnocení" mluví pro to, že bránou je Premium diváka, ne VIP inzerentky. **Stále čeká na vaše rozhodnutí.**
5. **Stránka FAQ** se má v administraci jmenovat „Časté dotazy".

---

## 7. Co jsem neověřil

Aby bylo jasné, kde audit končí:

- **Barvy, fonty, odsazení a poloměry** systematicky. Z JPEG exportu jde odečíst geometrie, ne hodnoty proměnných. Na to je potřeba Dev Mode, tedy placený seat.
- **Pixelové rozměry uvnitř účtu a mobilních obrazovek.** Ověřoval jsem obsah, strukturu a stavy — ne odsazení. Geometrii mám změřenou jen pro homepage, detail profilu, navigaci a patičku (minulý audit).
- **Stránka `prez`** — v tomto souboru neexistuje.
- **Chování prototypu** (přechody, interakce) — Figma prototype mode jsem neotevíral.

---

## 8. Co bych řešil v tomto pořadí

| # | Věc | Proč napřed | Kde |
|---|---|---|---|
| 1 | **Výplňový text ve dvou souhlasech** | souhlas psaný nesmyslem není souhlas | §2.3 |
| 2 | **Věková brána 18+** | jediná úplně chybějící obrazovka, u tohoto webu ne kosmetika | §2.1 |
| 3 | **Navigační lišta na 1920 px** | na první pohled viditelné, oprava je jednořádková | §6.1 |
| 4 | Zbylých devět výplňových textů | jsou v ostrém provozu | §2.3 |
| 5 | **Rozhodnout spor u sliderů** | potřebuji od vás výklad | §6.4 |
| 6 | **Rozhodnout ESCORT-ONLINE.COM** | potřebuji od vás, jestli je to druhá značka | §3 |
| 7 | Sjednotit lhůtu ověření (72 h vs. 24–48 h) | dvě hlášky vidí tentýž uživatel | §2.7 |
| 8 | „Recenze – již brzy" — popisek vs. chování | | §2.5 |
| 9 | Zarovnání patičky, šířka karty | | §6.2, §6.3 |
| 10 | Mobilní menu přihlášeného | potřebuji od vás rozhodnutí o rozsahu | §2.4 |
| 11 | Přejmenovat rámce ve Figmě | ne kód, ale ušetří to příští audit | §4.9 |

---

## 9. Otevřené otázky

Tři věci, které za vás rozhodnout nemůžu:

1. **Slidery** — má být rozostření vázané na VIP inzerentky, nebo na Premium účet diváka? (§6.4)
2. **ESCORT-ONLINE.COM** — druhá značka, nebo zbytek šablony? (§3)
3. **Mobilní menu přihlášeného** — držet se čtyř položek z návrhu a nechat část účtu na mobilu nedostupnou, nebo ponechat plný výčet? (§2.4)
