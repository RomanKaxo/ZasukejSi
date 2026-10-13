# Služby a hláška o dokončení profilu – 13. října 2026

- Seznam služeb vykresluje každou skutečnou službu jednou. Odstraněno
  cyklení stejného seznamu do 28 polí; volby mají stabilní klíč podle ID.
- `ServicesSeeder` doplňuje chybějící položky i do částečně naplněné databáze.
  Existující položku rozpozná podle českého nebo anglického názvu v JSON.
  Zachovává její ID, překlady, popis, pořadí, aktivaci i vazby profilů.
  Opakované spuštění nevytváří další kopie.
- `DatabaseSeeder` i `deploy.sh` používají společný `ServicesSeeder`.
  Katalog není definovaný dvakrát. Existující vlastní služby se nemažou.
- Růžová hláška o dokončení profilu používá souměrné sloupce pro ikonu,
  text a zavření. Text je uprostřed na desktopu i mobilu, pruh nepřetéká
  ani při šířce 320 px. Odkazy na chybějící údaje a zavření zůstávají funkční.

## Ověření

Nové regresní testy před opravou zachytily nedoplněný katalog a 14 kopií
jedné služby. Měření v prohlížečích před opravou zachytilo posun textu o 16 px
a přetékání pruhu na úzkém mobilu.

Po opravě prošly kontroly prázdného a částečného katalogu, opakovaného
spuštění, anglické položky, zachování administrátorských úprav a vazeb
a jediného přepínače pro každou službu. Kompletní PHPUnit: **811 testů**,
bez chyb a selhání; devět existujících upozornění na zastarávající deklarace.

Produkční build, kompilace Blade, Pint seederu a testu a syntaxe deploymentu
byly ověřeny. Proklikávání v Chromiu i WebKitu zahrnuje nové opravy, původní
profilové cesty a šířky 1440, 1024, 768, 375 a 320 px. WebKit je na Linuxu;
nejde o test konkrétní verze Safari na macOS.

Samostatné doplnění katalogu po aktualizaci aplikace:

```sh
php artisan db:seed --class=ServicesSeeder --force
```
