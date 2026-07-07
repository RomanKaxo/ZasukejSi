# GEMINI.md - ZasukejSi projektová pravidla

## Zásady pro úpravu souborů
- NIKDY, NIKDY, NIKDY nepřidávej ani neupravuj soubory, o kterých jsi nebyl přímo instruován uživatelem. Pracuj striktně pouze se soubory, které jsou součástí zadaného úkolu.

## Zásady pro indexování a čtení souborů
- Pouze se dívej na soubor, na který mě odkážeš, a indexuj/čti pouze ten. Nezkoumej neaktivní části codebase bez přímého příkazu.

---

# GLOBÁLNÍ PRAVIDLA (Platí pro všechny projekty)

## Subagenti — VŽDY zkontrolovat jako první krok
- Před tím, než začneš cokoliv řešit sám, zkontroluj, jestli je pro daný typ
  úkolu k dispozici specializovaný subagent (např. z nainstalovaného
  agent packu - frontend, debugging, security, atd.).
- Pokud existuje subagent, který svým zaměřením odpovídá zadanému promptu
  (např. debugging/hledání chyb, úprava frontend komponent, review kódu),
  deleguj úkol na něj místo obecného postupu.
- Pokud si nejsi jistý, který subagent se hodí, nebo žádný přesně nesedí,
  řekni to uživateli a navrhni nejbližší vhodnou alternativu, než začneš
  postupovat sám.
- Teprve pokud žádný relevantní subagent není dostupný, pokračuj standardním
  postupem podle pravidel níže.

## Obecná pravidla
- Odpovídej a komentuj kód v češtině, pokud není řečeno jinak.
- Před většími změnami navrhni plán a počkej na schválení.
- Nepřepisuj existující konvence v kódu bez upozornění.

## Diagnostika a debugging (důležité)
- Při hledání chyby/stringu/patternu v kódu VŽDY nejdřív použij cílený
  grep/search na přesný text nebo vzorec (např. konkrétní URL, chybovou
  hlášku, název souboru) - NIKDY neprocházej soubor po souboru "od oka".
- Pokud uživatel dodá konkrétní detail (URL, error kód, přesný text),
  MUSÍ se použít přesně tento detail při hledání, ne obecný popis problému.
- Rozděl úkol na kroky: 1) diagnostika/hledání, 2) ukázat nález PŘED
  úpravou a počkat na potvrzení, 3) provést úpravu, 4) ověřit výsledek
  (build, testy, network tab, apod.).
- Neuprav nic navíc mimo zadaný scope a nedávej vlastní vylepšení
  nad rámec zadání.
- Na konci vždy shrň: CO bylo změněno, KDE (soubor + řádek) a PROČ to
  byl problém.

## Frontend / styling
- Pro hlavní layout kontejnery používej Tailwind `container mx-auto`
  s responzivním paddingem (`px-4 sm:px-6 lg:px-8`).
- Pro max-width jednotlivých bloků používej standardní Tailwind třídy
  (`max-w-md`, `max-w-2xl`, `max-w-screen-lg`, atd.).
- `clamp()` používej JEN pro plynulé škálování velikosti textu u velkých
  nadpisů / hero sekcí, ne pro celkové layouty nebo kontejnery.
- Nikdy nevytvářej vlastní pevné breakpointy mimo Tailwind default
  (sm/md/lg/xl/2xl).

## Struktura projektu
- Nové komponenty vytvářej ve stejném stylu a struktuře jako existující
  soubory v daném projektu.
- Sdílené utility patří do `/lib` nebo `/utils` (pokud projekt tuto
  strukturu používá).

## Testování a kvalita
- Po větší změně spusť lint/build, pokud je to v projektu dostupné.
- Neodstraňuj testy jen proto, aby prošly.

## Bezpečnost
- Nikdy necommituj API klíče, tokeny ani jiné citlivé údaje.
- Pokud narazíš na hardcoded secret, upozorni na to.
