# Segmenty profilů — design

## Cíl
Umožnit adminům označovat profily (`Profile`) štítky "segmenty" (např. "Nová", "Ověřená", "Top lokalita") a automaticky odvozovat segment "VIP" z aktivního předplatného. Segmenty se zobrazí na kartě profilu, detailu profilu, ve filtru vyhledávání a v adminu — bez zásahu do existujícího layoutu/chování.

## Kontext a existující vzory
- Vzor kategorizace v projektu: `Service` model + pivot `profile_service` (M:N, `sort_order`, `is_active`, translatable `name`). Segmenty kopírují tuto strukturu (`app/Filament/Resources/Services/*` jako vzor pro Filament v4 resource: `ServiceResource.php`, `Schemas/ServiceForm.php`, `Tables/ServicesTable.php`, `Pages/{List,Create,Edit}Service.php`).
- VIP stav je dnes odvozený z `Profile::activeSubscription()` (`Profile::isVip()`, `Profile::scopeVip()`), starý sloupec `is_vip` byl záměrně odstraněn ve prospěch relačního výpočtu — segment "VIP" se drží stejného principu a **neukládá se** do `profile_segment`.
- DB je MySQL/MariaDB — standardní Laravel migrace, žádná Postgres syntaxe.
- Frontend: Blade + Livewire + Alpine, žádný SPA framework. Segmenty se přidávají jako nový vizuální prvek (badge) do existujících Blade partials, ne jako přepis layoutu.

## Datový model
- Tabulka `segments`: `id, name (translatable přes spatie/laravel-translatable), slug, color, icon, sort_order, is_active, timestamps`.
- Pivot `profile_segment`: `profile_id, segment_id, timestamps`, unique index `(profile_id, segment_id)`, cascade delete na oba FK.
- Model `Segment`: `HasFactory, HasTranslations`, `belongsToMany(Profile::class)`.
- `Profile` model: relace `segments()` (belongsToMany), metoda `allSegments()` vracející kolekci sloučenou z `segments()->where('is_active', true)` + syntetický "VIP" segment (pokud `isVip()`), bez duplicit, jednotný tvar (name, slug, color, icon) i pro syntetický VIP záznam.

## Admin (Filament v4)
- `SegmentResource` podle vzoru `ServiceResource` (CRUD: name, slug, color, icon, sort_order, is_active), oprávnění přes Filament Shield stejně jako u `Service`.
- `ProfileResource`: multi-select pro ruční přiřazení segmentů (relace `profile_segment`), VIP zobrazen jako read-only badge/info, needitovatelný přes tento formulář. Sloupec se segmenty (badge) a filtr podle segmentu v `ProfilesTable`.

## Veřejný frontend
- Badge segmentů (barva/ikona z modelu) na kartě profilu ve výpisu a na detailu profilu — přidáno do existujících Blade partials, žádný jiný prvek se nemění.
- Filtr podle segmentu ve vyhledávání/výpisu — nová query scope na `Profile`, napojená na existující filtrovací mechanismus (nezdvojovat).

## Testy
- Feature testy: CRUD segmentu v adminu; přiřazení/odebrání segmentu profilu; `allSegments()` vrací VIP i ruční segmenty bez duplicit; neaktivní segment se nezobrazuje; filtr podle segmentu funguje; smazání segmentu/profilu neprodukuje osiřelé pivot řádky ani chybu na frontendu.

## Postup realizace (3 fáze, každá musí projít testy než se pokračuje dál)
1. **Implementace** — migrace, modely, `SegmentResource`, rozšíření `ProfileResource`, seeder pár výchozích segmentů, feature testy.
2. **Audit** — samostatná kontrola vlastní implementace: N+1 dotazy (eager loading `allSegments()` všude, kde se použije), autorizace v adminu, bezpečné mazání/deaktivace bez chyb na frontendu, translatable fallback, indexy na pivot tabulce, pokrytí edge-case testy. Nálezy se opraví v kódu před další fází.
3. **Propojení napříč webem** — teprve po čistém auditu: karta profilu, detail profilu, filtr vyhledávání, admin tabulka. Po každém kroku spustit test suite a vizuálně ověřit, že se nic stávajícího nerozbilo.

## Akceptační kritéria
- Všechny nové i existující testy procházejí.
- Žádná stávající stránka/komponenta nezměnila chování mimo přidání segmentů.
- Admin může vytvářet/mazat/přiřazovat segmenty bez zásahu do kódu.
- VIP segment se zobrazuje automaticky a mizí s vypršením předplatného.
- Segmenty jsou viditelné a filtrovatelné na listing stránce, detailu profilu a v adminu.

## Mimo rozsah
- Žádná další automatická pravidla segmentace kromě VIP (lze doplnit později jako samostatný spec).
- Žádná migrace na jinou databázi (Supabase) — vyhodnoceno a zamítnuto jako nesouvisející a bez přínosu pro tento projekt.
