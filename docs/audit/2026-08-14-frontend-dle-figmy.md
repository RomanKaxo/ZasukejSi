# Frontend dotažený podle Figmy

**Datum:** 14. 8. 2026
**Testy:** `128 prošlo (321 assertions)`. `translations:audit` prochází. Všechny kontrolované URL vracejí 200.

---

## 1. Zámek hodnocení — klíčová mechanika návrhu

Návrh drží hodnocení za zámkem a klíčem je Premium členství. Implementace to měla **přesně obráceně**: rozmazávala fotku a hodnocení ukazovala.

| | Bylo | Nyní |
|---|---|---|
| Fotka | rozmazaná u ~poloviny karet (`crc32($id) % 2`) | vždy ostrá |
| Hodnocení | viditelné všem | zamčené, dokud `Gate::allows('view-ratings')` neprojde |
| Pilulka | jednolitá #E6FEE8 s číslem | dvoutónová **#D9D9D9 / #E8E8E8** s růžovým zámkem |

Ověřeno na úvodní stránce jako host: **25 zamčených hodnocení, 0 odemčených, 0 rozmazaných fotek, 0 zámků přes fotku**.

Varianta `vip-detail` zůstala nedotčená — používá ji slider na detailu profilu.

### Další vymyšlená data, která se přitom našla

```php
$rating = $profile->getTotalRatings() > 0
    ? $profile->getAverageRating()
    : (4.5 + ($profile->id % 5) * 0.1);   // ← známka odvozená z ID
```

Profil **bez jediného hodnocení** dostal známku 4,5–4,9 spočítanou ze svého ID. Odtud ta 4,6/5 a 4,9/5 na kartách. Nyní se u neohodnoceného profilu zobrazí prázdný stav.

Tohle jsem v dřívějších auditech minul — díval jsem se na `livewire/profile-card.blade.php`, což je osiřelý soubor; živá karta je `components/profile-card.blade.php`.

---

## 2. Premium banner — z pěti kopií na jednu komponentu

Blok „Vaše Premium členství platí do **12. 12. 2025**" byl **pětkrát zkopírovaný** (archive, favorites, girls-of-month, ratings, reported), pokaždé s pevným datem a nepřeloženým textem.

Nyní `x-premium-banner` v `layouts/member.blade.php`. Vykreslí se **jen členovi, který členství skutečně má**, s reálným datem z `User::membershipEndsAt()`. Geometrie beze změny.

---

## 3. Mrtvé odkazy

| Odkaz | Kam vede teď |
|---|---|
| „Obnovit přístup" (2×) | stránka VIP a Premium |
| „Dát hodnocení" (2×) | hodnotící obrazovka s předvybraným profilem; host dostane přihlašovací modál |
| „Začít PRÉMIUM" | stránka VIP a Premium |
| Obchodní podmínky, Ochrana údajů | `/terms-of-service`, `/privacy-policy` |

`MemberRatings::mount()` nově čte `?profile=`, takže odkaz otevře rovnou ten profil, ze kterého návštěvník přišel.

Všechny cíle se hledají přes `Page::published()`, takže se z odkazu nikdy nestane 404, když admin stránku odpublikuje — spadne na homepage.

**Zbylé `href="#"` jsou v pořádku:** čtyři mají `@click.prevent` (přihlašovací a nahlašovací modál) a jeden je záměrně vypnutá položka „Recenze – již brzy".

---

## 4. Tři jazyky

Přepínač byl na **čtyřech místech** natvrdo pro cs/en. Všechna nyní čtou z `config/locales.php`:

- rozbalovací menu v hlavičce (desktop)
- vlajka aktuálního jazyka v hlavičce
- jazyky v patičce
- jazyky v mobilním menu

Ověřeno: na stránce se vykreslují `cs`, `en` i `ru`. Nový jazyk se přidá jedním záznamem v configu, bez zásahu do šablon.

---

## 5. Rozměry — změřeno proti exportu 1:1

| Prvek | Figma | Před | Nyní |
|---|---|---|---|
| Filtrační pilulka — výška | 33 | 41 | **33** ✅ |
| Mezera mezi kartami | 21,5 | 23,6 | **21,5** ✅ |
| Rozteč karet | 231,5 | 233,6 | **231,5** ✅ |
| Mezera dlaždic galerie | 10 | 14 | **10** ✅ |
| Rozteč řádků parametrů | 36 | 21,6 → 54 | **36** ✅ |
| Výška řádku ceníku | 36 | 45,5 | **35,5** ✅ |

Mřížka karet je nyní `repeat(5, 210px)` s mezerou 21,5 px = **1136 px** — přesně šířka eco pruhu v návrhu, což potvrzuje, že je to zamýšlená obsahová šířka.

U rozteče řádků parametrů bylo potřeba opravit dvě věci: řádek měl 21,6 px místo 36, a `.vip-profile-meta-table` k tomu přidávala 18px mezeru, takže po prvním zásahu vyšlo 54. Nyní mají řádky pevných 36 px a mezera je 0.

---

## 6. Co zůstává

| Co | Poznámka |
|---|---|
| Toggle switche ve filtrech | `x-toggle-switch` existuje; vyžaduje přestavbu filtrační řady na přepínače |
| Pořadí prvků v kartě (B2) | **návrh si odporuje sám** — hlavní mřížka má hodnocení na 3. pozici, druhá řada na 5. Neměnil jsem bez rozhodnutí. |
| Velikost nadpisů sekcí (B13) | odvozeno z rozměru textového boxu, ne z `font-size`; chce potvrdit z Dev Mode |
| Stránka se seznamem členských plánů | backend hotový (checkout, webhook, admin), chybí jen výpis plánů |
| Mobilní verze | 27 rámců Figmy, zatím neověřeno |

---

## 7. Ověřeno

```
php artisan test               → 128 prošlo (321 assertions)
php artisan translations:audit → prochází

200 /              200 /?locale=ru    200 /countries
200 /profiles/21   200 /vip-premium   200 /faq
200 /terms-of-service              200 /register
```

Úvodní stránka jako host: mezera karet 21,5 · pilulka 33 · sloupce 5×210 px · 25 zamčených hodnocení · 0 rozmazaných fotek · vlajky cs/en/ru.
Detail profilu: galerie 337/537/337 s mezerami 10 · rozteč parametrů 36 · řádek ceníku 35,5 · panel 261.
