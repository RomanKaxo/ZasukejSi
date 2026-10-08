# Opravy podle klientské zpětné vazby – 8. října 2026

Změny reagují na devět dodaných videí. Účet poskytovatelky, veřejný profil,
karty a oblíbené používají stejné veřejné jméno a vybranou hlavní fotografii.

## Změněné chování

- Přepínač jazyků má stabilní klikací oblast, zůstává otevřený při výběru
  a při změně jazyka zachovává ostatní parametry URL. Hlavička již ve WebKitu
  neořezává vykreslení rozbalené nabídky.
- Pole přezdívky poskytovatelky upravuje veřejné `display_name`. Základní
  údaje zobrazují aktuální e-mail účtu; změna e-mailu dál používá samostatný
  ověřovací postup.
- Majitelka může přes „Můj profil“ otevřít i vlastní neveřejný nebo dosud
  neschválený profil. Náhled má vysvětlení, ostatní návštěvníci k němu přístup
  nedostanou a vlastní návštěva se nezapočítává do statistik.
- Nabídka účtu používá Poppins bez problematických ligatur.
- Správa fotografií umožňuje zvolit hlavní fotografii. Ta je první také
  ve veřejné galerii, na kartách a v oblíbených. Neplatné ID fotografie
  nevymaže původní volbu.
- Služby a jazyky se ukládají okamžitě a stránka posune potvrzení do viditelné
  oblasti. Dvě nefunkční tlačítka pro jejich uložení byla odstraněna.
- Dostupnost se ukládá až potvrzením. Po změně se ihned objeví upozornění
  a fialové tlačítko s bílým textem. Šedý stav nastane až po úspěšném uložení.
  Chybný interval zůstane rozpracovaný; vymazaný den zůstane prázdný i po reloadu.
- Oba desktopové grafy mají kompaktní rozestupy přibližně pro 15 čitelných
  dnů současně. Delší měsíc lze vodorovně posouvat bez vynechávání popisků.
  Mobilní přepínání měsíců a přepnutí velikosti okna fungují. Aktuální měsíc
  obsahuje pouze skutečně uplynulé dny.
- Odstranění BOM v šabloně formuláře opravuje následné ukládání přes Livewire.
  Opravena je také cesta k ikoně šipky po produkčním sestavení.

## Ověření

Provedeno lokálně s PHP 8.3.32, Node.js 22.20.0, SQLite a oddělenými
testovacími účty a fotografiemi:

- Instalace zámků závislostí, migrace databáze a produkční `npm run build`.
- Kompletní PHPUnit: **807 testů, 1 973 assertions, bez chyb a selhání**.
  Zůstává devět existujících upozornění PHPUnit na zastarávající deklarace.
- Sedm nových regresních testů v `ClientFeedbackRegressionTest`.
- **Pět kompletních kol v Chromiu a pět ve WebKitu**, vždy v obou prohlížečích:
  přepínání jazyků, opakované uložení formuláře, odkaz na vlastní profil,
  služby a jazyky, ruční potvrzení dostupnosti, odmítnutí chybného intervalu,
  vymazaný den, hlavní fotografie, veřejná karta, oba grafy, mobilní navigace
  a přesný profil v oblíbených po dvou obnoveních stránky.
- Rozlišení 1440, 768 a 375 px; žádné chyby JavaScriptu ani lokální HTTP
  odpovědi 4xx/5xx během uvedených cest.
- Nabídka jazyků je kontrolována i přes barevné pixely vykreslených vlajek;
  původní ořez ve WebKitu tuto kontrolu nesplnil.
- Skutečné nahrání nové fotografie přes UI v obou prohlížečích.
- Kontrola služeb a jazyka na veřejném profilu po uložení.
- Kompilace Blade šablon, audit překladů, kontrola whitespace diffu
  a Pint nového testu.

WebKit běžel na Linuxu; toto ověření nenahrazuje test konkrétní verze Safari
na macOS. Samotnou záměnu identity v oblíbených se na aktuální základní verzi
nepodařilo zopakovat; nový regresní test používá odlišná ID uživatele, profilu
a vazby a hlídá jméno, fotografii i odkaz po opakovaném načtení.

Existující soubory PHP mají stejné čtyři nálezy Pint jako nezměněný základ;
neprováděla se plošná změna formátování. Composer upozorňuje na již existující
nesoulad hashe zámku, závislosti nebyly v rámci těchto oprav aktualizovány.

Pro opakování backendových kontrol po instalaci a sestavení:

```sh
php -d memory_limit=1G vendor/bin/phpunit
php artisan view:cache
php artisan translations:audit
```
