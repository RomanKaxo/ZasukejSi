# Scraper

Konfigurovatelný scraper pro sekundární projekty. Nový web se přidává jako **řádek v databázi a sada selektorů**, ne jako nová třída.

---

## Jak to funguje

```
scrape_sources        web, adresa, adaptér, nastavení (prodleva, stránkování, obrázky)
  └─ scrape_field_maps    selektor → naše pole (+ transformace)
scrape_runs           jeden běh: kdy, kolik stránek, kolik nového, co selhalo
scrape_items          jeden nalezený profil, čeká na kontrolu
```

Běh **nikdy nezapisuje do `profiles`**. Položka se stáhne jako `pending`, člověk ji schválí a teprve pak z ní vznikne profil — a i ten je nepublikovaný a ve stavu ke schválení. Na web se nic nedostane bez dvou vědomých kroků.

---

## Spuštění

```bash
php artisan scrape:run eurogirlsescort-cz --dry-run --limit=3
```

| Přepínač | K čemu |
|---|---|
| `--dry-run` | stáhne a vyextrahuje, nic neuloží — na ladění selektorů |
| `--url=…` | jeden konkrétní profil místo procházení výpisu |
| `--limit=N` | zastaví po N detailech |
| `--pages=N` | kolik stránek výpisu projít |

Vypnutý zdroj jde spustit jen s `--dry-run`. V administraci je u zdroje tlačítko **Zkušební běh** se stejnými volbami.

Každý běh si zapisuje, co dělal — které stránky výpisu přečetl, kolik na nich bylo odkazů, co si vyžádal robots.txt a u zkušebního běhu jaké hodnoty selektory vrátily. V administraci je to u běhu pod **Průběh**; ladit selektory se dá odtud, bez konzole.

---

## Import do profilů

Schválená položka se stane profilem. V administraci je to akce u řádku a hromadná akce u výběru; u větší sklizně se hodí konzole, protože stahování fotek čeká prodlevu podle zdroje a u dvou tuctů profilů to trvá čtvrt hodiny.

```bash
php artisan scrape:import eurogirlsescort-cz --approve
```

| Přepínač | K čemu |
|---|---|
| `--approve` | vezme i čekající položky, ne jen schválené |
| `--limit=N` | zastaví po N položkách |
| `--without-images` | přeskočí stahování fotek |
| `--skip-duplicates` | vynechá položky označené jako možná duplicita |

Duplicita se před každým importem ověří znovu — uložený nález je snímek z okamžiku stažení a profil vzniklý tímtéž během v něm není. Jedna chybná položka nezastaví zbytek; skončí ve stavu `failed` s důvodem.

**Co vznikne, je nepublikované a ve stavu ke schválení.** Import přesouvá řádky do fronty, nedává nic na web.

---

## Duplicity

Scraper dřív poznal jen opakování sebe sama — tutéž `external_id` z téhož zdroje. Tatáž žena inzerovaná na druhém webu, nebo už existující jako profil u nás, prošla bez povšimnutí a import z ní udělal druhý profil.

Při stažení se u položky vyhodnotí, jestli nejde o někoho, koho už máme. Porovnává se v tomto pořadí:

| Signál | Síla | Jak |
|---|---|---|
| Telefon | silný | posledních devět číslic, takže `+420 777 123 456` a `777123456` je totéž |
| Jméno i město | střední | bez diakritiky, velikosti písmen a mezer — `Anička` = `ANICKA` |
| Jen jméno | slabý | pracovní jméno sdílí spousta dívek, takže je to nápověda, ne tvrzení |

Existující profil má přednost před jinou položkou ve frontě: profil už na webu je, takže druhý import je ta skutečná chyba. Zamítnutá položka se jako duplicita nenabízí — zamítnutí bylo rozhodnutí a tohle by ho vracelo zpět.

Nález se ukládá k položce, aby šel filtrovat bez dotazu na každý řádek. Je to snímek z okamžiku stažení; profil vzniklý později v něm není, proto je ve frontě hromadná akce **Znovu zkontrolovat duplicity**. Před importem se nález připomene v potvrzovacím okně.

Nic se neblokuje automaticky. Kontrola hlásí kandidáty s důvodem, rozhoduje pořád člověk.

---

## Automatické spouštění

Zdroj se sám nespouští, dokud mu nenastavíte interval (**Zdroj → Automatické spouštění**). Bez něj se chová jako dosud, tedy jen ručně.

| Pole | Význam |
|---|---|
| Interval (hodiny) | prázdné = žádné automatické spouštění; 24 znamená jednou denně |
| Další běh | prázdné a s vyplněným intervalem = hned při nejbližší kontrole |
| Stránek výpisu na běh | prázdné = `max_pages` z nastavení zdroje |
| Maximálně profilů na běh | prázdné = bez omezení |

Vypnutý zdroj se nespustí ani s intervalem — stejná pojistka, jakou má runner.

Plánovač kontroluje každou hodinu a spustí jen zdroje, jejichž slot nastal:

```bash
php artisan scrape:due
```

| Přepínač | K čemu |
|---|---|
| `--source=slug` | omezí na jeden zdroj |
| `--force` | spustí zapnuté zdroje bez ohledu na plán |

Vyžaduje cron `* * * * * php artisan schedule:run`, stejně jako životní cyklus předplatných. Běh, který selže, si slot přesto posune — jinak by každá další kontrola začínala tímtéž rozbitým zdrojem.

---

## Ohleduplnost k cílovému webu

Zabudované, ne volitelné:

- **`robots.txt` se čte při každém běhu** a ukládá ke zdroji, takže je zpětně dohledatelné, co web požadoval.
- **Prodleva mezi požadavky je vyšší z dvojice** „nastavení zdroje" a „`Crawl-delay` z robots.txt". Zdroj lze zpomalit, nikdy zrychlit pod hodnotu, kterou web žádá.
- Zakázané cesty se nestahují; požadavek na ně skončí chybou, ne tichým přeskočením.
- Prodleva se počítá **zvlášť pro každý host**, aby stahování fotek z CDN nebrzdilo čtení stránek a naopak.
- Každý požadavek jde přes `HttpFetcher`, takže na tohle nelze zapomenout na volajícím místě.

U `eurogirlsescort.cz` robots.txt nezakazuje žádnou cestu a předepisuje `Crawl-delay: 5`. Zdroj má proto výchozí prodlevu 5 s.

---

## Přidání dalšího webu

1. **Administrace → Scraper → Zdroje → Nový.** Vyplňte adresu a nastavení:

   | Klíč | Význam |
   |---|---|
   | `listing_path` | cesta k výpisu, např. `/eskort` |
   | `pagination_param` | parametr stránkování (`page`) |
   | `pagination_pattern` | nebo cesta, např. `/page/{page}/` |
   | `detail_link_selector` | selektor odkazů na detail |
   | `detail_url_pattern` | regulární výraz, kterým se odkazy profiltrují |
   | `external_id_pattern` | odkud vzít ID profilu (kvůli deduplikaci) |
   | `image_selector`, `image_attribute` | kde jsou fotky |
   | `image_prefer_pattern` | která velikost, když web nabízí víc variant |
   | `crawl_delay`, `timeout`, `max_pages`, `image_limit` | limity |

2. **Mapování polí** — u zdroje záložka *Mapování polí*. Jeden řádek = jeden selektor a naše pole.

3. **Vyzkoušejte** `--dry-run --url=…` a selektory dolaďte.

4. Teprve pak zdroj **zapněte**.

Web s neobvyklým výpisem dostane vlastní adaptér: implementujte `SourceAdapter` a zaregistrujte ho v `AdapterRegistry`.

---

## Transformace

Spouštějí se v zadaném pořadí. Bez argumentu jako `"trim"`, s argumentem jako `["regex", "/(\\d+)/"]`.

`trim` · `collapse_whitespace` · `lower` · `upper` · `strip_tags` · `digits` · `int` · `float` · `boolean` · `regex` · `replace` · `prefix` · `suffix` · `absolute_url` · `map` · `first` · `compact` · `unique` · `strip_invisible` · `reject`

**`strip_invisible`** odstraní mezery nulové šířky, spojovníky a BOM. Některé weby je sypou do textů proti kopírování; přežijí `trim` a skončí v profilu jako neviditelný chuchvalec na konci popisu.

**`reject`** zahodí celé položky odpovídající vzoru — `["reject", "/^\\d+\\s*Hodiny$/ui"]`. Běží nad seznamem dřív než transformace po prvcích, protože maže položky místo aby je přepisovala. Hodí se, když jeden selektor sbírá víc tabulek najednou: na eurogirlsescort.cz sdílí tabulka služeb značkování s otevírací dobou a ceníkem, takže mezi službami skončí „Úterý" a „12 Hodiny".

**Časté zaseknutí:** selektor jako `table tr` vrátí desítky řádků a `regex` proběhne na každém, takže většina vyjde prázdná. `compact` je zahodí, teprve pak má `first` smysl:

```json
[["regex", "/(\\d{3})\\s*cm/u"], "compact", "first", "int"]
```

---

## Deduplikace a opakované běhy

Položka je jednoznačná dvojicí zdroj + `external_id`. Další běh:

- **beze změny** (shodný hash obsahu) — jen se poznamená, že profil byl viděn
- **změněný** — přepíše se a vrátí ke kontrole
- **zamítnutý nebo importovaný** — zůstává, další běh ho do fronty nevrátí

---

## Fronta „K doplnění"

Scraper nikdy nerozšiřuje náš číselník sám. Když zdroj nabídne hodnotu, kterou nevedeme, uloží se do fronty a rozhodne o ní člověk v administraci pod **Scraper → K doplnění**.

Fronta pokrývá **všechna pole, která umíme zpracovat**, ne jen služby:

| Pole | Kam se hodnota doplní |
|---|---|
| `services` | Služby |
| `city` | Města |
| `bust_size`, `bust_type`, `eye_colour`, `hair_colour`, `hair_length`, `pubic_hair`, `travels` | Vlastnosti profilů |

Pole s pevnou množinou (ISO kód země, pohlaví) žádný číselník nemají — u nich se tlačítko „Doplnit do systému" nenabízí, protože není co rozšířit.

Jak to běží:

1. Běh scraperu položku zpracuje a chybějící hodnoty zapíše do fronty. Pravopis nedělá druhý záznam — „GFE - společnice" a „gfe spolecnice" jsou jedna položka, jen se zvýší počet výskytů.
2. **Hodnota, kterou neznáme, se na profil neuloží.** Kdyby se uložila, select v administraci by u ní ukazoval prázdno a další uložení by ji smazalo. Čeká ve frontě.
3. Položka s mezerou je nekompletní — nabízí se u ní „doplnit chybějící hodnoty" místo jednoklikového schválení.
4. Jakmile někdo hodnotu doplní, uvolní se položky, které na ni čekaly, a **profily, které už vznikly, hodnotu dostanou** bez opakovaného importu.

Doplnění už vytvořených profilů jde spustit i ručně:

```bash
php artisan scrape:unknown-values --resync
```

Další volby: `--list` jen vypíše, co chybí (nic nemění), `--approve-all` doplní všechno najednou — hodí se po prvním velkém sběru, kdy je katalog prázdný.

Hodnoty, které katalog zná, spravuje administrace v **Vlastnosti profilů**. Odtud je bere i formulář profilu — velikost prsou bývala natvrdo psané pole `['A'..'H']` na dvou místech.

---

## Právní stránka

Scraper stahuje **osobní údaje a cizí fotografie**. Sám o sobě neřeší, jestli je smíte použít — to je rozhodnutí na vás a nesouvisí s tím, že běh technicky projde.

Co je proto zabudované, aby to bylo obhajitelné:

- **Nic se nepublikuje automaticky.** Import vytvoří nepublikovaný profil ve stavu ke schválení.
- **Původ se neztrácí.** Každá položka si drží zdrojovou URL a každá stažená fotka má v `custom_properties` adresu, odkud pochází, a ID položky.
- **Smazání je jednoduché.** Fotky i profily jdou dohledat podle zdroje a odstranit.

Před ostrým nasazením doporučuji vyjasnit si právní základ zpracování osobních údajů a autorská práva k fotografiím. U profilů reálných osob v citlivé oblasti to není formalita.
