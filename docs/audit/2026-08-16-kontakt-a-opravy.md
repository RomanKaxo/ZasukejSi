# Kontaktní formulář a čtyři opravy

**Datum:** 16. 8. 2026

---

## 1. VIP karty v nejlépe hodnocených jsou prokliknutelné

Rozostření a odkaz visely na jednom příznaku:

```php
$profileUrl = $shouldBlur ? '#' : route('profiles.show', $profile);
```

Rozostření je upoutávka, ne zábrana — návštěvník se musí na profil dostat, aby zjistil, co si odemyká. Adresa se počítá nezávisle a z tlačítka „Detail" zmizelo `pointer-events: none`. Rozostřená zůstává fotka a jméno, jak bylo.

**Ověřeno:** `ProfileCardVipBlurTest` (3 testy) a v prohlížeči na `/profiles/1` — 6 karet, všechny VIP, všechny rozostřené, všechny s adresou na svůj profil a klikatelné.

---

## 2. „Obnovit přístup" vede na obnovu hesla

Tlačítko odkazovalo na stránku VIP & Premium. Nyní je to obnova hesla.

Formulář `forgot-password` je za `guest` middlewarem, takže přihlášeného návštěvníka by odrazil na homepage. Odkaz se proto větví:

| Kdo | Kam |
|---|---|
| Nepřihlášený | `/forgot-password` |
| Přihlášený člen | `/account/member/password` |
| Přihlášená slečna / admin | `/account/password` |

Popisek už není závislý na členství — „Přístup platný do…" zůstal jen u sliderů, kam patří (proměnná přejmenována na `$membershipValidLabel`, aby bylo zřejmé, co znamená).

**Ověřeno:** `ProfileDetailRestoreAccessTest` (3 testy) a v prohlížeči — mobilní i desktopová varianta odkazu míří na `/forgot-password`.

---

## 3. Ruská vlajka

`cs.png` a `en.png` jsou předkulacené obrázky 26×26 s průhlednými rohy. `ru.png` byl plný obdélník 60×40 — proto se jako jediný nezakulatil a navíc ho `object-fit: cover` ořezával jinak než ostatní.

Vygeneroval jsem `ru.png` ve stejné geometrii: 26×26, kruhový výřez trikolóry (#FFFFFF / #0039A6 / #D52B1E), hrana vyhlazená vykreslením v osminásobku a zmenšením. **Žádná změna CSS** — riziko pro layout nulové.

`LocaleFlagAssetTest` nově hlídá, že vlajka každého jazyka z `config/locales.php` je čtvercová, s průhledným rohem a neprůhledným středem. Nová jazyková mutace s obdélníkovou vlajkou tedy shodí test, ne až vzhled webu.

---

## 4. Kontaktní formulář

### Frontend

Formulář se vykresluje na stránce se slugem `contact`, stejným mechanismem jako výpis předplatných na `vip-premium` — podle slugu, ne podle značky v obsahu, aby ho editor nemohl omylem smazat úpravou textu.

Pole: jméno, příjmení, telefon (nepovinný), e-mail, zpráva. Přihlášenému se předvyplní z účtu a formulář nahlas píše, co se ke zprávě připojí („Odesíláte jako …", „Zpráva bude spojena s profilem …") — přihlášený odesílatel nemá být překvapený, že víme, kdo je.

Omezení: 5 odeslání z jedné IP za 10 minut.

### Co se ukládá

Tabulka `contact_messages`:

| Skupina | Sloupce |
|---|---|
| Z formuláře | `first_name`, `last_name`, `phone`, `email`, `message` |
| Přihlášený odesílatel | `user_id`, `profile_id` (obojí `nullOnDelete`) |
| Zpracování | `status`, `read_at`, `admin_note` |
| Kontext | `locale`, `ip_address`, `user_agent`, `created_at` |

`user_id` a `profile_id` se při smazání účtu nulují, nekaskádují — zpráva sama je záznam, který má smazání účtu přežít.

### Administrace

`/admin/contact-messages` ve skupině „Komunikace":

- výpis s filtry na stav a na nepřečtené, nepřečtené tučně
- odznak v navigaci počítá **nepřečtené**, ne všechny
- detail ukazuje odesílatele, jeho účet a profil (proklik do profilu), zprávu a sbalený kontext odeslání
- otevření detailu zprávu označí za přečtenou
- akce: odpovědět e-mailem (`mailto:`), označit za přečtené, smazat
- editovatelné jsou jen stav a interní poznámka; text odesílatele je jen ke čtení
- zprávu nelze zakládat ručně — chodí z veřejného formuláře

**Ověřeno:** `ContactFormTest` (8 testů) a `ContactMessageAdminTest` (5 testů), `/admin/contact-messages` přidáno do `AdminPagesRenderTest`.

---

## 5. Co jsem nemohl ověřit

**Kontaktní formulář nemá předlohu ve Figmě.** Zadání ho vyžádalo, v návrhu není. Vzhled jsem odvodil z modálu pro nahlášení profilu — stejná typografie Poppins, stejné barvy (#F2F2F2 panel, #DD3888 tlačítko, #E6E6E6 rámečky, 8px rádius polí, 15px rádius panelu), aby nepůsobil jako cizí prvek. **Pokud pro něj předloha existuje, pošlete ji a rozměry srovnám.**

Změřeno: 720 px na 1280 px šířky, dva sloupce; pod 640 px jeden sloupec; žádný vodorovný přetok v obou případech.

---

## 6. Vedlejší nález z předchozí dávky

Při psaní testu ke kartám vyšlo najevo, že `Currency::options()` vrací při prázdné tabulce měn prázdné pole, přestože výběr měny ve formuláři profilu je povinný — na čerstvé instalaci by nešel uložit žádný profil. Chybí-li aktivní měna, zastupuje ji koruna.

---

## 7. Stav

`php artisan test` — **292 prošlo**
`php artisan translations:audit` — prošel

Zbývá z dřívějška: ruský překlad na 128 z 1675 klíčů (čeká na vaše rozhodnutí), měření mobilní verze pod y = 900 u detailu profilu, dva strukturální rozdíly v mobilu popsané v [2026-08-16-mobil-detail-profilu.md](2026-08-16-mobil-detail-profilu.md).
