# Plnohodnotné otestování scraperu

Postup na jedno posezení, asi hodina a půl. Každý krok má **co udělat** a **podle čeho se pozná, že to je dobře** — bez toho druhého se testuje jen to, že nic nespadlo.

Píše se to pro dev.stanektech.cz. Na produkci se to dá projet stejně, ale kroky 6 a 9 tam něco stáhnou z cizího webu, takže tam radši až po dev.

---

## 0. Než se začne

```bash
php artisan migrate
php artisan translations:import
```

Bez migrací nefunguje nic z posledních kol (zdraví zdrojů, historie změn, opakování, hlídka zmizelých, platební metody).

**Kontrola:** v administraci existuje **Scraper → Dílna** a **Nastavení → Platební metody** a ani jedna stránka nehlásí chybu.

---

## 1. Ověřit, že web vůbec odpovídá

**Scraper → Zdroje → řádek zdroje → Otestovat spojení.**

- **Dobře:** „Web odpověděl, staženo N znaků."
- **Špatně: HTTP 403.** Pak rovnou **Diagnostika spojení** — projde žebřík pokusů a řekne, na které příčce to prošlo a co uložit do nastavení. Detaily v [scraper.md](scraper.md#když-web-vrací-403).

  Není to chyba v kódu: z mého stroje ta samá adresa odpovídá 200 se všemi kombinacemi hlaviček, které jsem zkusil, takže rozhoduje to, odkud se ptáte — a diagnostika se ptá odsud.

**Dokud tenhle krok nefunguje, další kroky nemají co testovat.**

---

## 2. Dílna: co ten web vlastně je

**Scraper → Dílna → vybrat zdroj → Prozkoumat web.**

Projít výpis a odpovědět si na čtyři otázky:

| Co je v reportu | Co s tím |
|---|---|
| **robots.txt** — počet zákazů, crawl-delay | Když je crawl-delay vyšší než nastavení zdroje, platí ten vyšší. Sedí to? |
| **Sitemapa** — nalezena / nenalezena | Když je, přepnout `discovery` na `sitemap` a ušetřit si selektor odkazů i stránkování |
| **Skupiny odkazů** | Nejpočetnější skupina má být profily. Selektor a filtr adres z tabulky jdou vzít a vložit do nastavení zdroje |
| **Co web zveřejňuje sám o sobě** | Když jsou tam `jsonld:` klíče, jsou lepší než selektory — přežijí redesign |

**Dobře:** report ukáže alespoň jednu skupinu odkazů se třemi a víc výskyty a navržený filtr adres sedí na skutečnou adresu profilu.

**Špatně:** „Na stránce se nenašla žádná skupina podobných odkazů" + „Stránka se skládá až v prohlížeči". Pak je potřeba buď `json:` selektory z výpisu, nebo `render_endpoint`.

---

## 3. Dílna: co vrací selektory

**Dílna → do pole adresa vložit adresu jednoho profilu → Vyzkoušet selektory.**

Tabulka má dva sloupce vedle sebe a v tom je celý smysl:

- **Nalezeno** prázdné → selektor nesedí,
- **Nalezeno** plné a **Po transformacích** prázdné → selektor sedí a hodnotu zahodila transformace.

Dřív obojí vypadalo stejně.

**Dobře:** všechna povinná pole mají hodnotu v obou sloupcích.

Přepínač **Použít uloženou stránku** zkouší selektory na tom, co scraper naposledy stáhl — bez dotazu na cizí web. Napoprvé tam ještě nic uloženého není, takže se to stáhne; pod tabulkou je napsané, odkud HTML bylo.

---

## 4. Zkušební běh na jednom profilu

**Zdroje → Zkušební běh** → vyplnit URL jednoho profilu, limit 1, **Jen zkouška zapnuto**.

**Dobře:** hláška „Běh dokončen" a v **Scraper → Běhy** je u toho běhu v průběhu vypsané, co selektory vrátily, včetně hodnot.

**Kontrola, že zkouška je opravdu zkouška:** v **Ke kontrole** nepřibyla žádná položka.

---

## 5. Ostrý běh na pár profilech

**Zdroje → Stáhnout z odkazu** → odkaz na výpis, 1 stránka, limit 3.

Mezi dotazy se čeká podle `crawl_delay`, takže tři profily trvají desítky sekund. To je správně.

**Dobře:**
- v **Ke kontrole** jsou tři nové položky ve stavu *Ke kontrole*,
- u každé je v detailu vidět jméno, město, cena a seznam fotek,
- **žádný profil na webu nevznikl** — import je samostatný krok.

---

## 6. Opakovaný běh: co se nemá dít

Spustit **totéž znovu**, stejné zadání.

**Dobře:**
- v průběhu běhu je u položek „beze změny", ne „aktualizováno",
- počet položek v **Ke kontrole** zůstal tři, nezdvojnásobil se,
- u zdroje s `conditional_requests` je v logu „beze změny (hlásí web)" — to znamená, že se stránka vůbec nestahovala.

**Špatně:** šest položek. To by znamenalo, že `external_id_pattern` z adresy netahá stabilní identifikátor.

---

## 7. Historie změn

Tohle chce trochu trpělivosti nebo malý podvod:

1. V **Ke kontrole → detail položky** si poznamenat cenu.
2. V databázi u té položky změnit v `normalized` cenu na jinou (nebo počkat, až se změní na webu).
3. Spustit běh znovu.

**Dobře:** v detailu položky v záložce **Co se změnilo na zdroji** je řádek s polem, původní a novou hodnotou. U ceny má být vykřičník „stojí za pozornost".

**Dobře i to, co tam není:** noční běh nad nehybným webem historii nenafoukne — bez změny se řádek nezakládá.

---

## 8. Import do profilu

U jedné položky **Schválit**, pak **Vytvořit profil** (fotky zapnuté).

**Dobře:**
- vznikl profil ve stavu *Čeká na schválení*, **nepublikovaný**,
- má stažené fotky,
- v **Profily → detail → Zdroje profilu** je vidět, ze kterého webu pochází.

**Kontrola duplicit fotek:** u téže položky kliknout **Doplnit z aktuálních dat**. Fotek nesmí přibýt — hláška řekne, kolik jich profil už měl.

---

## 9. Jedna dívka, dva weby

Pokud je nastavený druhý zdroj:

1. Stáhnout tutéž dívku z druhého webu.
2. U nové položky **Připojit k existujícímu profilu** — pokud kontrola duplicit zabrala, profil je předvyplněný.

**Dobře:**
- **nevznikl druhý profil**,
- v **Zdroje profilu** jsou teď dva řádky,
- co bylo v profilu vyplněné, se nepřepsalo.

---

## 10. Věková pojistka

V **Dílně** není co testovat; tohle se testuje na datech.

1. U existující položky **Ke kontrole** změnit v `normalized` věk na 17.
2. Spustit běh znovu (nebo zkusit položku importovat).

**Dobře:** položka je **Zamítnuto** s hláškou „pod hranicí 18 let" a importovat ji nejde. Ani po ruční úpravě, ani připojením k profilu.

To je jediná věc v celém scraperu, která se nedá obejít nastavením: `minimum_age` jde jen zvýšit.

---

## 11. Vlastní pravidla

U zdroje do **Vlastní pravidla** napsat:

```
city != Brno
```

Spustit běh na výpisu z jiného města.

**Dobře:** položky jsou **Zamítnuto** s hláškou „Odmítnuto pravidlem zdroje: city != Brno".

Pak zkusit nesmysl (`tohle není pravidlo`) — formulář to při uložení odmítne uložit s uvedením řádku.

Nakonec pravidlo smazat, ať to nepřekáží dalším testům.

---

## 12. Hlídka předělaného webu

1. U zdroje záměrně rozbít selektor povinného pole (třeba `h1` → `h1.neexistuje`).
2. Spustit běh na výpisu s deseti profily.

**Dobře:**
- běh **skončí chybou** „vypadá to, že se web předělal",
- nedojel do konce — položek přibylo míň, než kolik bylo adres,
- **existující položky si podržely svoje hodnoty** a mají zapsaný neúspěšný pokus.

Pak selektor opravit a spustit znovu — položky se srovnají.

---

## 13. Opakování selhaných adres

1. Spustit běh na výpisu, kde jedna adresa vede na 404 (nebo dočasně rozbít jednu adresu).
2. V **Ke kontrole** zapnout filtr **Čeká na další pokus**.

**Dobře:** položka tam je, má **Pokusy 1** a poznámku, kdy se zkusí znovu (za 15 minut).

Hromadnou akcí **Zkusit stáhnout hned** ji zařadit dopředu a spustit běh — adresa jde na řadu první.

---

## 14. Zdraví zdroje a pauza

1. U zdroje nastavit `failure_threshold` na 1.
2. Dočasně zadat nesmyslnou `base_url`, aby běh selhal.
3. Spustit běh.

**Dobře:**
- ve výpisu zdrojů je stav **pozastaveno** s důvodem,
- zdroj vypadl z plánu (`scrape:due` ho přeskočí a napíše proč),
- v **Oznámeních** je zpráva pro administraci — **a na webu ji nikdo nevidí**.

Pak `base_url` opravit a spustit ručně: pauza se sama zruší.

Nakonec vrátit `failure_threshold` na 3.

---

## 15. Kontrola existence profilů

```bash
php artisan scrape:verify --limit=5
```

Na správně fungujícím zdroji nemá nic zmizet. Otestovat to jde tak, že se u importované položky změní `source_url` na neexistující adresu a příkaz se pustí dvakrát (jedna 404 nestačí, potvrzení jsou dvě).

**Dobře:**
- v **Ke kontrole** filtr **Zmizely ze zdroje** ukáže položku,
- na nástěnce je dlaždice **Zmizely ze zdroje**,
- **profil je pořád publikovaný a nesmazaný** — čeká na rozhodnutí,
- tlačítka **Ponechat profil** / **Skrýt profil** fungují a skrytí profil archivuje, nemaže.

---

## 16. Automatika

U zdroje nastavit **Interval 24 h** a okno **od 2 do 6**.

```bash
php artisan scrape:due
```

**Ve dne dobře:** „Zdroj … čeká na své okno — otevře se zítra 02:00."

```bash
php artisan scrape:due --force --source=<identifikátor>
```

**Dobře:** proběhne bez ohledu na okno.

**Kontrola, že cron vůbec běží:**

```bash
php artisan schedule:list
```

Musí tam být `scrape:due` (hodinově), `scrape:verify` (4:20), `scrape:prune` (pondělí 3:40) a `notifications:archive` (4:50).

---

## 17. Úklid

```bash
php artisan scrape:prune --dry-run
```

**Dobře:** vypíše, co by smazal, a **nesmaže nic**. Stažené položky ani profily se nemažou nikdy — to je sklizeň, ne účetnictví.

---

## Co se tímhle neotestuje

Buďme přesní v tom, co postup nepokrývá:

- **Chování při dlouhém běhu.** Tři profily nejsou pět set. Časy, paměť a to, jestli hosting nezabije proces, ukáže až ostrý běh na celé město.
- **Weby s obsahem jen z JavaScriptu.** Dílna je pozná a řekne to, ale jestli `render_endpoint` opravdu funguje, se ověří až s konkrétní vykreslovací službou.
- **Souběh dvou serverů.** Zámek zdroje je přes cache; při více serverech musí být cache sdílená (Redis), jinak se dva běhy potkají.
- **403 z produkční IP.** To se dá ověřit jedině z produkce, viz krok 1.
