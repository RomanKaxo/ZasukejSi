# Audit účtů, předplatných a rozdělení podle pohlaví

**Datum:** 16. 8. 2026
**Rozsah:** stránka VIP & Premium, oblast `/account`, dámské vs. pánské rozhraní

---

## 1. VIP & Premium — doplněno

Stránka o předplatných o nich jen psala. Oba produkty byly dostupné **výhradně zevnitř účtu**, takže návštěvník, který si o nich přečetl, neměl kudy dál.

Nyní vypisuje skutečné plány z databáze, rozdělené podle publika:

| Publikum | Plány | Co obsahují |
|---|---|---|
| **Ženy** (`audience = profile`) | VIP, Premium, Elite, Premium roční | zvýraznění profilu, přední umístění, odznak ověření, statistiky, přednostní podpora |
| **Muži** (`audience = member`) | Premium 30 / 60 / 90 / 180 / 365 dní | hodnocení dívek, archiv, dívky měsíce |

Ceny se berou ve měně podle jazyka návštěvníka, z vlastního sloupce — nepřepočítávají se kurzem.

**Tlačítko se řídí tím, kdo se dívá.** Host dostane výzvu k registraci, žena odkaz na svá předplatná, muž na své členství. Kdo je přihlášený a dívá se na plán druhého publika, dostane větu, že plán je pro opačné pohlaví — místo tlačítka, které by nemohlo fungovat.

Vykresluje se podle slugu `vip-premium`, ne podle značky v obsahu, takže editor nemůže plány omylem smazat úpravou textu.

---

## 2. Rozdělení dámského a pánského rozhraní

Ověřeno testem u **každé** podstránky obou oblastí.

### Žena — správa profilu (`/account/…`)

| Stránka | Stav |
|---|---|
| Nástěnka, Fotografie, Služby, Statistiky, Recenze, Předplatné, Profil, Heslo | vše se vykresluje |

### Muž — členství (`/account/member/…`)

| Stránka | Stav |
|---|---|
| Nástěnka, Hodnocení, Oblíbené, Dívky měsíce, Archiv, Nahlášené, Členství, Heslo | vše se vykresluje |

### Oddělení je vynucené

- Muž na `/account` je přesměrován na svou nástěnku — nedostane obrazovku profilu, který nemá
- Žena na `/account/member/…` je odmítnuta
- Admin prochází do ženské větve, jak je popsáno v `routes/web.php`

---

## 3. Patička na `/account`

**Nepotvrdil jsem poškození.** `layouts/account` dědí z `layouts/app`, který patičku obsahuje, a test ověřuje, že se na `/account` skutečně vykreslí uzavírací značka `</footer>`.

Možná vysvětlení, která bych rád ověřil s vámi:

1. **Chybí build.** `public/build` je gitignorovaný; bez `npm run build` po nasazení chybí CSS a patička se rozsype. Tohle považuji za nejpravděpodobnější.
2. Poškození je vizuální při konkrétní šířce okna — potřeboval bych vědět jaké.
3. Jde o jinou podstránku než `/account`.

Pošlete prosím snímek nebo šířku okna; bez toho hádám.

---

## 4. Co jsem nemohl ověřit proti Figmě

Uvádím to na vaši výslovnou žádost.

- **Rozvržení `/account` proti Figmě** — v exportu, který mám (`C:\Users\medion\Desktop\xxx`), jsou desktopové rámce homepage a detailu profilu a 31 mobilních rámců. **Rámec pro `/account` v desktopové sadě neumím jednoznačně identifikovat.** Ověřoval jsem tedy funkčnost, ne shodu s návrhem.
- **Vzhled stránky VIP & Premium** — návrh výpisu plánů jsem v exportu nenašel. Použil jsem tvarosloví karet z členské stránky plánů, aby to nebylo cizí prvek. Pokud v Figmě podoba existuje, pošlete rámec a srovnám ji.
- **Mobilní podoba obou oblastí** — z 31 mobilních rámců jsem ověřil homepage, mřížku karet a menu. Členské ani profilové stránky ověřené nejsou.

---

## 5. Stav

| Ukazatel | Hodnota |
|---|---|
| **Testy** | **262 prošlo** (586 asercí) |
| `translations:audit` | prochází |
| Nové testy v této etapě | 23 |

---

## 6. Zůstává otevřené

1. **Ruština — 128 klíčů z 1 675.** Buď doplnit, nebo přepínač skrýt.
2. **Mobil — pět oblastí ze šesti neověřeno.**
3. **Stripe není nakonfigurovaný** — bez webhook secretu nelze koupit nic, protože aktivace je vázaná na ověřený webhook.
4. **Členský formulář stále ukládá dostupnost ve starém plochém tvaru** — čtení ho srovná, uložení tvar znovu rozbije. Oprava znamená zásah do frontendu.
5. **Anglická značka** — Figma má escort-online.com, implementace jedno logo pro všechny jazyky.
