# Implementace chybějících funkcí — členství, Gate, jazyky

**Datum:** 14. 8. 2026
**Zadání:** implementovat funkce z auditu a propojit je napříč systémem, **bez zásahu do frontendu**.
**Testy:** `116 prošlo (296 assertions)` — z toho 13 nových. `translations:audit` prochází.
**Šablony:** ani jeden `.blade.php` nezměněn.

---

## 1. Členské předplatné (A1) — hotovo

Nejzávažnější nález auditu: návrh je postavený na placeném členství pro muže, které v systému neexistovalo ani datově.

### Datová vrstva

| Migrace | Co dělá |
|---|---|
| `2026_08_14_000004_add_audience_to_subscription_types_table` | rozlišení plánů: `profile` (VIP pro dívky) vs. `member` (Premium pro muže). Vše stávající se backfillne na `profile`, takže žádný existující dotaz nemění význam. |
| `2026_08_14_000005_create_member_subscriptions_table` | `user_id`, `subscription_type_id`, `starts_at`, `ends_at`, `status`, `cancelled_at`, `auto_renew`, `metadata` + index `(user_id, status, ends_at)` |

**Proč nová tabulka, a ne polymorfní přepis `subscriptions`:** sloupec `subscriptions.profile_id` figuruje na 11 místech včetně `Profile::isVip()`, VIP řazení v `ProfileList` a několika Filament resources. Rozšiřování by je všechny vystavilo riziku bez užitku.

### Model `MemberSubscription`

Záměrně zrcadlí `Subscription` — stejné konstanty stavů, stejné názvy scopů, stejná slovesa `renew()` / `cancel()` / `expire()`. Notifikuje při aktivaci, obnovení, vypršení i zrušení a sám se přepne na `expired`, když datum uplyne.

### `User` API

```php
$user->memberSubscriptions()   // všechna členství
$user->activeMembership()      // to, které právě platí
$user->hasActiveMembership()   // N+1-safe, respektuje withExists('activeMembership as has_membership')
$user->membershipEndsAt()      // datum pro banner „platí do …"
$user->canSeeRatings()         // admin | poskytovatelka | aktivní členství
```

`hasActiveMembership()` používá stejný vzorec jako `Profile::hasActiveSubscription()` — když si volající předpočítá `has_membership`, nespustí se dotaz na řádek.

---

## 2. Zamykání hodnocení (A2) — logika hotová

```php
Gate::define('view-ratings', fn (?User $user) => $user?->canSeeRatings() ?? false);
```

Host nikdy, člen jen s aktivním členstvím, poskytovatelka a admin vždy.

> **Šablony zatím zámek nevykreslují** — to je zásah do frontendu, který jste vyloučil. Gate je připravený; stačí ho v kartě a na detailu profilu použít.

---

## 3. Stripe pro členy (A1) — hotovo

Nový `MembershipCheckoutController` vedle stávajícího, protože ten profilový běží za middleware `gender:female` + `profile.exists` a člen profil nemá.

| Routa | Co dělá |
|---|---|
| `POST account/member/membership/{subscriptionType}/checkout` | vytvoří Stripe session, ověří že plán je členský a aktivní |
| `GET account/member/membership/success` | ověří session u Stripe, rozliší *zaplaceno a aktivní* / *zaplaceno, čeká na webhook* / *neověřeno* |
| `GET account/member/membership/cancel` | zrušení |

**Webhook je sdílený.** Stripe posílá na jednu URL, takže `SubscriptionCheckoutController::webhook()` rozlišuje podle metadat: `member_user_id` → členství, `profile_id` → profilový VIP tarif.

Ošetřeno:
- **idempotence** — opakovaný webhook nevytvoří druhý záznam (Stripe retry)
- **prodloužení místo duplicity** — nákup během platného členství přičte dny k existujícímu datu
- **kontrola publika** — profilovým plánem nelze koupit členství

Controller **nevykresluje žádné view** — `success` a `cancel` přesměrují na dashboard s flash zprávou. Proto se nedotkl frontendu.

---

## 4. Administrace (A1) — hotovo

- **`MemberSubscriptionResource`** ve skupině „Předplatná": tabulka se stavem, dobou do konce, filtrem „brzy končí", akcemi *Obnovit* / *Zrušit* a badge v navigaci s počtem končících do 7 dnů.
- **`SubscriptionTypeResource`** dostal pole *Určeno pro* — jedno místo pro oba druhy plánů.
- **Widget nástěnky** nyní počítá končící předplatná z obou zdrojů a rozepisuje je: `Profily: X · Členství: Y`.
- **`MemberSubscriptionTypeSeeder`** — tři plány (měsíc 299 Kč, čtvrtletí 749 Kč, rok 2 490 Kč), registrovaný v `DatabaseSeeder`.

Ověřeno: `/admin/member-subscriptions` se registruje, label „Členská předplatná", skupina „Předplatná".

---

## 5. Ruský jazyk (A3) — infrastruktura hotová

Seznam jazyků byl natvrdo na **pěti místech**. Centralizován do `config/locales.php` + `App\Support\Locales`:

| Bylo | Je |
|---|---|
| `SetLocale`: `in_array($locale, ['en','cs'])` | `Locales::isSupported($locale)` |
| `AppServiceProvider`: `->locales(['en','cs'])` + ruční labels/flags | odvozeno z configu |
| `CountryStatsService::LOCALES` | `Locales::codes()` |
| `AuditTranslations` (2×) | `Locales::withTranslations()` |

`cs, en, ru` jsou zapnuté. **Obsah překladů zatím chybí** — `lang/ru/` neexistuje, takže ruština padá na `fallback_locale`, což je funkční, ne rozbitý stav. Audit překladů proto kontroluje jen jazyky, které soubory skutečně mají, a nehlásí falešně celý projekt jako nepřeložený.

**Ruské texty jsem nepsal ručně** — nekvalitní překlad je horší než žádný. Projekt má na to `translations:sync-en` přes DeepL; po doplnění `DEEPL_API_KEY` lze stejným způsobem vygenerovat i ruštinu.

Chybí ještě `public/flags/ru.png` (config na něj odkazuje).

---

## 6. Nové testy (13)

`tests/Feature/MemberSubscriptionTest.php`:

- oddělení plánů podle publika; staré plány defaultují na `profile`
- člen bez členství hodnocení nevidí, s členstvím ano, po vypršení zase ne
- poskytovatelka a admin vidí vždy; Gate odmítá hosta
- webhook aktivuje členství z zaplacené session
- opakovaná session nevytvoří druhý záznam
- nákup během platnosti prodlouží, nezaloží druhé členství
- profilovým plánem nelze koupit členství
- aktivace pošle notifikaci
- checkout poskytovatelky nabízí jen profilové plány

---

## 7. Co zůstalo neuděláno — vyžaduje zásah do frontendu

| # | Co | Proč nebylo |
|---|---|---|
| A2 (UI) | zámek místo hodnoty v kartě a na detailu | šablona |
| A4 | toggle switche ve filtrech (`x-toggle-switch` existuje) | šablona |
| A5 | 6 mrtvých odkazů `href="#"` | šablona |
| A6 (UI) | banner „platí do …" — 5× duplikovaný blok s pevným datem `12. 12. 2025` | šablona; **data už jsou hotová** přes `membershipEndsAt()` |
| A3 (UI) | přepínač 3 jazyků v mobilním menu | šablona |
| A1 (UI) | stránka se seznamem členských plánů + oprava „Začít PRÉMIUM" | šablona |
| — | vlajka `public/flags/ru.png` | asset |
| — | ruské překlady | vyžaduje `DEEPL_API_KEY` |

Backend je připravený na všechny tyto body — chybí jen napojení v šablonách.

---

## 8. Ověření

```
php artisan test              → 116 prošlo (296 assertions)
php artisan translations:audit → prochází
php artisan route:list --name=membership → 3 routy
```

Členské plány v DB: 3 · profilové: 4 · locales: `cs, en, ru` · s překlady: `cs, en`.
