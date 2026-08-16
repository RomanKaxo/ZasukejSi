# Patička, scraper a administrace

**Datum:** 16. 8. 2026

---

## 1. Patička je celá z administrace

### 1.1 Menu

Patička uměla vypsat jen CMS stránky pod jejich vlastními názvy. Nešlo tedy mít odkaz pojmenovaný jinak než stránka, dva odkazy na jednu stránku, ani odkaz mimo web — což byly přesně tři otázky otevřené v minulém auditu.

Nová entita `FooterMenuItem`: popisek (překládaný), cíl (stránka nebo vlastní adresa), sloupec, pořadí, otevření v novém panelu, viditelnost. **Obsah → Menu v patičce**, s přetahováním pořadí.

- Dokud v menu není ani jedna položka, patička vypisuje stránky jako dosud. **Nasazením se nic neztratí.**
- `FooterMenuSeeder` z těch stránek udělá první uspořádání a nikdy nepřepíše to, co si admin nastavil.
- Odkaz na odpublikovanou nebo smazanou stránku se vynechá.

Tím padá i překážka popsaná dřív: pořadí patičky už nesdílí `sort_order` s horní navigací.

### 1.2 Texty

Logo, box o diskrétnosti, ekologický štítek a copyright byly natvrdo v šabloně. Jsou to obyčejné překladové klíče — měnit je šlo i dřív, jen se muselo vědět které. **Obsah → Texty v patičce** je sbírá na jedno místo v pořadí, v jakém je patička kreslí, se záložkou pro každý jazyk.

Logo bylo dvě natvrdo psané varianty za `@if(locale === 'en')`. Zůstávají tři části i barvy (#5C2D62 / #DD3888 / #8C8C8C); ruština, která dosud spadla do české větve, má teď vlastní hodnoty.

---

## 2. Scraper

### 2.1 Fronta rozhodovala naslepo

Položka nabízela **Schválit** a **Vytvořit profil**, ale nikdy neukázala, co obsahuje — rozhodovalo se podle jména a města.

Detail položky nyní ukazuje:

| Sekce | Obsah |
|---|---|
| Fotografie | náhledy přímo ze zdroje — jinak je není kde vidět, stahují se až při importu |
| Údaje profilu | pole, která importér zapisuje, **editovatelná před importem** |
| Ostatní hodnoty | vše, co extrakce našla nad rámec těch polí |
| Původ | zdroj, běh, zdrojová adresa, ID u zdroje, chyba, odkaz na vzniklý profil |

Opravy jsou revizorovy, ne zdrojovy: `raw` zůstává nedotčený, aby ručně opravená hodnota nepředstírala, že ji řekl zdroj. Po importu se úpravy skryjí.

### 2.2 Běh říkal počty, ne co viděl

Runner se celou dobu popisuje — které stránky výpisu přečetl, kolik na nich bylo odkazů, co si vyžádal robots.txt a u zkušebního běhu **jaké hodnoty selektory vrátily**. Ten popis se dostal jen do konzolové úlohy; z administrace se počítal a zahazoval, takže zkušební běh uměl říct „nalezeno 3" a nic víc.

Nový sloupec `scrape_runs.log` (strop 500 řádků) a akce **Průběh**. Zkušební běh tak konečně dělá to, kvůli čemu existuje.

### 2.3 Ostatní

- hromadné **Vytvořit profily z vybraných** — import po jednom znamenal u padesáti profilů padesát potvrzení; jedna chybná položka nezastaví zbytek a důvod zůstane na ní
- **Vrátit ke kontrole** pro selhané a zamítnuté — selhaný import je většinou opravitelná hodnota, ne mrtvý řádek
- proklik z běhu na jeho položky
- u zdroje sloupec **Poslední běh** se stavem a chybou v tooltipu
- ve frontě náhled první fotky, věk u jména, filtr **Bez fotografií**, proklik na vzniklý profil
- chyba u běhu má tooltip s celým textem (byla oříznutá na 40 znaků bez možnosti si ji přečíst)

---

## 3. Tabulky v administraci

### 3.1 Profily

| Přidáno | Proč |
|---|---|
| Náhled fotky | seznam jmen; profil se pozná podle fotky |
| Sloupec VIP s datem konce v tooltipu | jestli profil platí, bylo vidět až uvnitř záznamu na záložce |
| Počet fotek, červeně při nule | profil bez fotky se prakticky nedá zveřejnit |
| Hodnocení (průměr a počet) | dosud jen v detailu |
| Filtry: čeká na schválení, bez fotografií, s aktivním VIP | tři otázky, kvůli kterým se tato obrazovka otevírá |
| Hromadné schválení a zveřejnění | schvalování dávky importů po jednom byla nejpomalejší část importu |

Zveřejnění hromadně přeskočí neschválené profily — jinak by se na web dostaly dřív než jejich vlastní revize.

### 3.2 Uživatelé

| Přidáno | Proč |
|---|---|
| Profil jménem, s odkazem a stavem zveřejnění | byla to jen ikona ano/ne |
| Členství s datem konce | nejčastější dotaz podpory na účet |
| Filtry: pohlaví, neověřený e-mail, bez profilu | žádné kromě rolí neexistovaly |
| Akce „Ověřit e-mail" | běžná úloha podpory bez tlačítka |
| Kopírování e-mailu, jednotné české popisky | část hlaviček byla anglicky („Email Verified", „Has Profile", „No phone") |

---

## 4. Co jsem nedělal

- **Zbývající tabulky** (hodnocení, nahlášení, blog, notifikace, města, segmenty) jsem nechal beze změny. Jsou funkční a nedostal jsem k nim konkrétní zadání; sáhnout do všech naráz bez ověření by bylo riskantnější než užitečné.
- **Sada odkazů v patičce proti návrhu** — teď si ji můžete srovnat sám. Dvě otázky trvají: mají být „VIP účet pro dívky" a „Prémium účet pro pány" dvě samostatné stránky, a mají „Obchodní podmínky" v patičce být?
- **Plánování scraperu** (automatické spouštění v čase) — datový model na to nemá pole a nevím, jestli to chcete. Dnes se běh spouští z administrace nebo konzolovou úlohou.

---

## 5. Stav

`php artisan test` — **346 prošlo**
`php artisan translations:audit` — prošel

Na serveru je potřeba:

```bash
php artisan migrate
php artisan db:seed --class=FooterMenuSeeder
```
