# Prázdné hodnoty — závazná konvence

> Platí pro celý frontend. Vzniklo při dotažení projektu do produkce (plán z 14. 8. 2026).

## Pravidlo

**Žádná hodnota se nevymýšlí.** Údaj zobrazený návštěvníkovi je buď pravdivý
z databáze, nebo se nezobrazí vůbec — nikdy se nenahrazuje pravděpodobně
vypadajícím číslem.

Zároveň **se nic z frontendu neodstraňuje**. Dlaždice, sekce, tabulka i slider
zůstávají vykreslené; prázdná je jen hodnota uvnitř.

## Jak na to

```blade
{{-- ❌ ŠPATNĚ — vymyslí výšku každému, kdo ji nemá --}}
{{ $profile->content['card_height_cm'] ?? 168 }} cm

{{-- ❌ ŠPATNĚ — dlaždice zmizí, layout se pohne --}}
@if($profile->height)
    <div class="tile">{{ $profile->height }} cm</div>
@endif

{{-- ✅ SPRÁVNĚ — dlaždice zůstává, mizí jen hodnota --}}
<div class="tile">
    @if($profile->height)
        {{ $profile->height }} cm
    @else
        <x-empty-value />
    @endif
</div>
```

## Varianty

| Varianta | Výstup | Použití |
|---|---|---|
| `<x-empty-value />` | `—` | Kompaktní dlaždice: výška, váha, věk, cena, hodnocení |
| `<x-empty-value variant="text" />` | `Neuvedeno` | Širší bloky: jazyky, dostupnost, popis |

## Proč komponenta a ne prostý text

`empty-value.blade.php` **záměrně nemá vlastní `font-family`, `font-size` ani
`font-weight`** — vše dědí z rodičovského elementu. Box si proto zachová přesně
stejné metriky jako s reálnou hodnotou a layout se nehne o pixel. Ztlumení
řeší `currentColor` + `opacity`, takže funguje na světlém i tmavém podkladu.

## Časté chyby

- **Fallback obrázků.** `asset('images/models/model6.png')` jako náhrada za
  chybějící fotku je vymyšlená data — návštěvník uvidí cizí ženu u profilu,
  který fotku nemá. Použij neutrální placeholder.
- **Fallback v `lang/`.** `__('front.profiles.detail_page.services_default')`
  vypadá jako překlad, ale je to vymyšlený obsah.
- **Nenačtený sloupec.** Pokud dotaz sloupec neselektuje (`->select([...])`),
  je atribut `null` a fallback se uplatní *vždy* — i u záznamů, které data mají.
  Před přidáním prázdného stavu vždy ověř, že se sloupec do šablony vůbec
  dostane.

## Kontrola

```bash
php artisan test --filter=EmptyValue
php artisan translations:audit
```
