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

`trim` · `collapse_whitespace` · `lower` · `upper` · `strip_tags` · `digits` · `int` · `float` · `boolean` · `regex` · `replace` · `prefix` · `suffix` · `absolute_url` · `map` · `first` · `compact` · `unique`

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

## Právní stránka

Scraper stahuje **osobní údaje a cizí fotografie**. Sám o sobě neřeší, jestli je smíte použít — to je rozhodnutí na vás a nesouvisí s tím, že běh technicky projde.

Co je proto zabudované, aby to bylo obhajitelné:

- **Nic se nepublikuje automaticky.** Import vytvoří nepublikovaný profil ve stavu ke schválení.
- **Původ se neztrácí.** Každá položka si drží zdrojovou URL a každá stažená fotka má v `custom_properties` adresu, odkud pochází, a ID položky.
- **Smazání je jednoduché.** Fotky i profily jdou dohledat podle zdroje a odstranit.

Před ostrým nasazením doporučuji vyjasnit si právní základ zpracování osobních údajů a autorská práva k fotografiím. U profilů reálných osob v citlivé oblasti to není formalita.
