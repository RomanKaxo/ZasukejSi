<?php

namespace App\Support;

/**
 * Co se v které sekci administrace dá a nedá dělat.
 *
 * Většina obrazovek je pochopitelná, až když víte, co je za nimi: fronta
 * scraperu nepublikuje sama, hodnocení nejde přepsat ručně, číselník vlastností
 * řídí nabídku ve dvou různých formulářích. Tohle je ta znalost napsaná na
 * místě, kde ji člověk potřebuje.
 *
 * Klíč je poslední část URL panelu (`/admin/<klíč>`), takže nová sekce dostane
 * legendu přidáním jednoho záznamu, ne úpravou její třídy.
 */
class AdminGuides
{
    /**
     * @return array<string, array{
     *     intro: string,
     *     can?: array<int, string>,
     *     cannot?: array<int, string>,
     *     links?: array<string, string>
     * }>
     */
    public static function all(): array
    {
        return [
            '' => [
                'intro' => 'Přehled provozu: co čeká na vaše rozhodnutí a jak si web stojí. Čísla jsou proklikatelná — vedou rovnou na seznam, který danou věc řeší.',
                'can' => [
                    'Vidíte, co je rozdělané: profily ke schválení, nahlášení, nepřečtené zprávy, fronta scraperu.',
                    'Tržby počítají jen skutečně zaplacená předplatná.',
                ],
                'cannot' => [
                    'Předplatné přidělené bez platby se počítá mezi aktivní, ale ne do tržeb — má vlastní dlaždici, aby čísla šla srovnat.',
                ],
            ],

            'profiles' => [
                'intro' => 'Inzeráty dívek. Profil sem zakládá provozovatelka sama nebo ho vytvoří import ze scraperu; v obou případech čeká na vaše schválení a do té doby není veřejný.',
                'can' => [
                    'Schválit, zamítnout nebo zablokovat přímo v řádku — bez otevírání detailu.',
                    'Upravit cizí profil včetně ceníku a fotek. Každá úprava se zapíše do logu v detailu profilu.',
                    'Přidat VIP nebo jiné předplatné v záložce u profilu.',
                ],
                'cannot' => [
                    'Nastavit hodnocení — to vzniká z hodnocení členů a ručně se nepřepisuje.',
                    'Vrátit smazané fotky; odebrání z galerie je konečné.',
                ],
                'links' => [
                    'Zobrazení profilů' => 'profile-views',
                    'Vlastnosti profilů' => 'profile-attribute-options',
                    'Předplatná' => 'subscriptions',
                ],
            ],

            'profile-views' => [
                'intro' => 'Návštěvnost po profilech. Jeden řádek na profil, ne na návštěvu — sloupec s počtem za období je to, podle čeho se řadí „kdo je nejvíc vidět".',
                'can' => [
                    'Přepnout období: celkem, měsíc, čtvrt roku, půl roku, rok.',
                    'Seřadit podle zobrazení za období, celkem i podle kliků.',
                ],
                'cannot' => [
                    'Dohledat konkrétní návštěvu — jednotlivé záznamy se tu záměrně nevypisují.',
                    'Údaje mazat; návštěvnost je záznam o tom, co se stalo.',
                ],
                'links' => [
                    'Profily' => 'profiles',
                ],
            ],

            'profile-attribute-options' => [
                'intro' => 'Nabídky u vlastností profilu — barva očí, vlasy, prsa, ochlupení, cestování, jazyky. Řídí, z čeho se vybírá ve formuláři profilu i podle čeho scraper pozná, jestli hodnotu zná.',
                'can' => [
                    'Přidat hodnotu, přejmenovat ji nebo změnit pořadí v nabídce.',
                    'Vypnout hodnotu: přestane se nabízet, ale profilům, které ji mají, zůstane.',
                ],
                'cannot' => [
                    'Smazat hodnotu bez následku — sloupec „Použito u profilů" ukazuje, kolika profilů se to dotkne.',
                ],
                'links' => [
                    'Profily' => 'profiles',
                    'Hodnoty k doplnění' => 'scrape-unknown-values',
                ],
            ],

            'users' => [
                'intro' => 'Účty. Pohlaví rozhoduje o tom, co uživatel na webu vidí: ženy inzerují, muži hodnotí a kupují Premium.',
                'can' => [
                    'Změnit role. „Super admin" obchází kontroly oprávnění, dávejte ho opatrně.',
                    'Změnit e-mail a heslo.',
                ],
                'cannot' => [
                    'Přihlásit se za uživatele.',
                    'Smazat účet bez následku — smaže se s ním i jeho profil a inzerát.',
                ],
                'links' => [
                    'Role' => 'roles',
                    'Profily' => 'profiles',
                ],
            ],

            'roles' => [
                'intro' => 'Role a jejich oprávnění. Uložený název je strojový (`super_admin`), zobrazuje se čitelný.',
                'can' => [
                    'Vytvořit roli a nastavit jí oprávnění.',
                ],
                'cannot' => [
                    'Odebrat oprávnění roli „super_admin" — ta kontroly obchází ze své podstaty.',
                ],
                'links' => [
                    'Uživatelé' => 'users',
                ],
            ],

            'subscriptions' => [
                'intro' => 'Předplatná profilů — VIP a další tarify, které dívce zaplatí lepší viditelnost.',
                'can' => [
                    'Přidat nebo prodloužit předplatné ručně, když platba proběhla mimo systém.',
                ],
                'cannot' => [
                    'Vrátit peníze — vratky se řeší ve Stripe, tady se jen ukončí platnost.',
                ],
                'links' => [
                    'Typy předplatného' => 'subscription-types',
                    'Členská předplatná' => 'member-subscriptions',
                ],
            ],

            'member-subscriptions' => [
                'intro' => 'Premium členství pánů. Odemyká hodnocení dívek a archiv.',
                'can' => [
                    'Přidat nebo prodloužit členství ručně.',
                ],
                'cannot' => [
                    'Přidat členství ženě — je to produkt pro muže.',
                ],
                'links' => [
                    'Typy předplatného' => 'subscription-types',
                    'Uživatelé' => 'users',
                ],
            ],

            'subscription-types' => [
                'intro' => 'Tarify, které se nabízejí na stránce VIP & Premium. „Publikum" rozhoduje, komu se tarif ukáže — ženám, nebo mužům.',
                'can' => [
                    'Nastavit ceny zvlášť pro každou měnu a odrážky, které se vypíšou na kartě tarifu.',
                    'Tarif vypnout: přestane se nabízet, běžícím předplatným to nic neudělá.',
                ],
                'cannot' => [
                    'Změnit cenu zpětně — už zaplacená předplatná si pamatují, co za ně bylo strženo.',
                ],
                'links' => [
                    'Předplatná' => 'subscriptions',
                    'Členská předplatná' => 'member-subscriptions',
                ],
            ],

            'services' => [
                'intro' => 'Katalog služeb, ze kterého si dívky vybírají na svém profilu.',
                'can' => [
                    'Přidat službu, přejmenovat ji nebo změnit pořadí.',
                ],
                'cannot' => [
                    'Nechat scraper zakládat služby sám — nové názvy ze zdrojů čekají ve frontě na vaše rozhodnutí.',
                ],
                'links' => [
                    'Hodnoty k doplnění' => 'scrape-unknown-values',
                ],
            ],

            'scrape-sources' => [
                'intro' => 'Weby, ze kterých stahujeme. Každý zdroj má svá mapování polí — selektory, které říkají, kde na stránce co hledat.',
                'can' => [
                    'Spustit zkušební běh na jednom profilu a podívat se, co selektory vrátily.',
                    'Stáhnout celý výpis. Vše skončí ve frontě ke kontrole, nic se nepublikuje samo.',
                    'Nastavit pravidelné spouštění včetně hodin a dnů, kdy se smí stahovat. Ruční běh se oknem neřídí.',
                    'Vybrat, odkud se berou adresy: procházením výpisu, nebo ze sitemapy webu. Sitemapa umí i „jen to, co se od minule změnilo".',
                    'Stáhnout nastavení zdroje do souboru a jinde ho zase načíst — i s mapováním polí.',
                    'Zrušit pauzu u zdroje, který se sám pozastavil po opakovaných selháních.',
                    'Zkoušet selektory na naposledy uložené stránce — bez dalšího dotazu na cizí web (viz Dílna).',
                    'Napsat vlastní pravidla, čím se položka rovnou odmítne (spam v popisu, cizí město, chybějící telefon).',
                    'Číst data z webů, které se skládají až v prohlížeči — selektorem „json:…" z dat, která stránka veze s sebou.',
                ],
                'cannot' => [
                    'Obejít robots.txt ani nastavenou prodlevu mezi dotazy.',
                    'Stáhnout profil s uvedeným věkem pod 18 let. Ten se zablokuje při stahování i při importu a nastavením se to obejít nedá.',
                    'Spustit dva běhy téhož zdroje najednou — druhý se odmítne, dokud první nedoběhne.',
                    'Ztratit adresu, která se nepodařila stáhnout — zařadí se k dalšímu pokusu, s rostoucím odstupem.',
                    'Dojet běh, ve kterém najednou většina stránek nevrací povinná pole — to je předělaný web a běh se zastaví dřív, než přepíše profily prázdnem.',
                    'Držet v plánu zdroj, který opakovaně selhává — ten se sám pozastaví, aby web nedostával bota, kterého stejně odmítá.',
                ],
                'links' => [
                    'Stažené položky' => 'scrape-items',
                    'Běhy' => 'scrape-runs',
                    'Hodnoty k doplnění' => 'scrape-unknown-values',
                ],
            ],

            'scraper-workbench' => [
                'intro' => 'Zkoušení bez stahování. Stáhne se jedna stránka a odpoví na otázky, které se jinak zjišťují opakovanými zkušebními běhy.',
                'can' => [
                    'Prozkoumat web: robots.txt, sitemapa, skupiny odkazů vypadající jako profily a hotové návrhy selektoru i filtru adres.',
                    'Zjistit, co web zveřejňuje sám o sobě (JSON-LD, meta značky) — takové klíče jdou použít jako selektor a přežijí redesign.',
                    'Vyzkoušet všechna mapování polí na jedné adrese a vidět vedle sebe, co selektor našel a co z toho zbylo po transformacích.',
                ],
                'cannot' => [
                    'Nic uložit. Nevzniká tu položka ve frontě ani běh.',
                    'Spustit JavaScript — co se na stránce dotahuje až v prohlížeči, scraper nevidí.',
                ],
                'links' => [
                    'Zdroje' => 'scrape-sources',
                    'Stažené položky' => 'scrape-items',
                ],
            ],

            'scrape-items' => [
                'intro' => 'Fronta stažených profilů. Nic odsud neodejde na web bez vašeho schválení a i po importu je profil neveřejný.',
                'can' => [
                    'Rozhodnout o profilu, který ze zdroje zmizel: ponechat, nebo skrýt. Skrytí profil archivuje, nemaže ho.',
                    'V detailu položky vidět historii: co se na zdroji mezi běhy změnilo, z čeho na co a které fotky přibyly či zmizely.',
                    'Najít profily, u kterých se zdroj po importu změnil, a doplnit z aktuálních dat prázdná pole.',
                    'Připojit položku k existujícímu profilu místo zakládání druhého — tatáž dívka na třech webech je jeden profil se třemi zdroji.',
                    'Prohlédnout si, co scraper našel, a schválit nebo zamítnout.',
                    'Importovat schválené položky hromadně i s fotkami.',
                ],
                'cannot' => [
                    'Smazat profil za vás. Zmizení ze zdroje se jen označí a nahlásí — rozhodnutí je vždycky vaše.',
                    'Přepsat v profilu hodnotu, kterou jste upravili ručně. Doplnění plní jen prázdná pole.',
                    'Importovat položku, která zmiňuje hodnotu, kterou náš číselník nezná — nejdřív ji doplňte ve frontě hodnot.',
                ],
                'links' => [
                    'Zdroje' => 'scrape-sources',
                    'Hodnoty k doplnění' => 'scrape-unknown-values',
                    'Profily' => 'profiles',
                ],
            ],

            'scrape-runs' => [
                'intro' => 'Průběh jednotlivých běhů: co se stahovalo, jak dlouho to trvalo a co selhalo.',
                'can' => [
                    'Dohledat, proč zdroj přestal vracet data.',
                ],
                'cannot' => [
                    'Spustit běh odsud — to se dělá u zdroje.',
                ],
                'links' => [
                    'Zdroje' => 'scrape-sources',
                ],
            ],

            'scrape-unknown-values' => [
                'intro' => 'Hodnoty, které zdroj nabídl a náš číselník je nezná. Scraper nikdy nerozšiřuje číselník sám — rozhoduje se tady.',
                'can' => [
                    'Doplnit hodnotu do systému, případně ji přitom přejmenovat.',
                    'Zamítnout ji: položky, které ji zmiňují, zůstanou neúplné.',
                ],
                'cannot' => [
                    'Doplnit hodnotu u pole s pevnou množinou (ISO kód země, pohlaví) — není co rozšiřovat.',
                ],
                'links' => [
                    'Stažené položky' => 'scrape-items',
                    'Služby' => 'services',
                    'Vlastnosti profilů' => 'profile-attribute-options',
                ],
            ],

            'reports' => [
                'intro' => 'Nahlášení od přihlášených uživatelů.',
                'can' => [
                    'Vyřídit nahlášení a poznamenat, jak bylo vyřešeno.',
                ],
                'links' => [
                    'Anonymní nahlášení' => 'profile-reports',
                    'Profily' => 'profiles',
                ],
            ],

            'profile-reports' => [
                'intro' => 'Nahlášení od nepřihlášených návštěvníků. Nemají autora, takže se nedají doptat — posuzují se podle obsahu.',
                'links' => [
                    'Nahlášení' => 'reports',
                ],
            ],

            'contact-messages' => [
                'intro' => 'Zprávy z kontaktního formuláře. Nikam se nepřeposílají, takže nezodpovězená zpráva je vidět jen tady.',
                'can' => [
                    'Přečíst zprávu i s profilem odesílatele, pokud byl přihlášený.',
                ],
                'cannot' => [
                    'Odpovědět odsud — odpovídá se e-mailem.',
                ],
            ],

            'messages' => [
                'intro' => 'Zprávy mezi uživateli. Sem se chodí kvůli stížnostem a zneužití, ne kvůli běžnému provozu.',
                'cannot' => [
                    'Psát za uživatele.',
                ],
            ],

            'ratings' => [
                'intro' => 'Hodnocení dívek od členů. Ukládá se procento, hvězdy jsou jen jeho zobrazení.',
                'can' => [
                    'Smazat hodnocení, které porušuje pravidla.',
                ],
                'cannot' => [
                    'Změnit hodnotu — hodnocení patří tomu, kdo ho dal.',
                ],
                'links' => [
                    'Nastavení systému' => 'manage-settings',
                ],
            ],

            'notifications' => [
                'intro' => 'Oznámení pro uživatele. Globální oznámení vidí všichni; přečtení si systém pamatuje pro každého zvlášť.',
                'can' => [
                    'Poslat oznámení jednomu uživateli nebo všem.',
                ],
            ],

            'pages' => [
                'intro' => 'Obsahové stránky. „Zobrazit v menu" a „Zobrazit v patičce" řídí, kde se na ně odkazuje.',
                'can' => [
                    'Upravit obsah blokovým editorem a nastavit pořadí v menu i v patičce.',
                ],
                'cannot' => [
                    'Odstranit ze stránky VIP & Premium výpis tarifů ani z Kontaktu formulář — vykreslují se podle adresy stránky, ne podle obsahu.',
                ],
                'links' => [
                    'Menu v patičce' => 'footer-menu-items',
                    'Texty v patičce' => 'manage-footer',
                ],
            ],

            'blogs' => [
                'intro' => 'Články v sekci novinek na hlavní stránce.',
            ],

            'cities' => [
                'intro' => 'Města, ze kterých se vybírá u profilu a podle kterých se filtruje na webu. Kraj se čte z pole „Region".',
                'can' => [
                    'Přidat město, které v seznamu chybí.',
                ],
                'links' => [
                    'Země' => 'countries',
                ],
            ],

            'countries' => [
                'intro' => 'Země v postranním panelu a ve vyhledávání. Počty profilů se počítají z databáze; nula je platná hodnota a zemi ze seznamu nevyřadí.',
                'can' => [
                    'Změnit pořadí, přepsat název a zemi skrýt.',
                ],
                'links' => [
                    'Města' => 'cities',
                ],
            ],

            'segments' => [
                'intro' => 'Štítky, které se zobrazují na kartě profilu (VIP, ověřeno a další).',
            ],

            'translations' => [
                'intro' => 'Texty webu ve všech jazycích. Hodnota uložená tady přebíjí to, co je v jazykovém souboru.',
                'can' => [
                    'Přepsat kterýkoli text bez zásahu do kódu.',
                ],
                'cannot' => [
                    'Vrátit se k původnímu znění jinak než smazáním přepisu — sloupec s výchozí hodnotou ukazuje, co bylo dodáno.',
                ],
                'links' => [
                    'Texty v patičce' => 'manage-footer',
                    'Věková brána' => 'manage-age-gate',
                ],
            ],

            'currencies' => [
                'intro' => 'Měny a jejich formátování. Základní měna se používá tam, kde tarif nemá cenu ve zvolené měně.',
            ],

            'manage-payment-methods' => [
                'intro' => 'Čím se dá u nás zaplatit. Všechno tohle bylo dřív v souboru na serveru, takže zapnout platbu nebo opravit klíč znamenalo nasazení a člověka s přístupem do konzole.',
                'can' => [
                    'Zapnout nebo vypnout platbu kartou i převodem.',
                    'Zadat klíče Stripe — mají přednost před tím, co je v souboru na serveru.',
                    'Vyplnit bankovní údaje a doplňující pokyny, které kupující uvidí u platby.',
                ],
                'cannot' => [
                    'Nabídnout metodu bez údajů. Zapnutá metoda bez čísla účtu je cesta, která nikam nevede — stránka to po uložení řekne.',
                    'Aktivovat předplatné placené převodem. To potvrzuje člověk u konkrétní objednávky, až peníze dorazí.',
                ],
                'links' => [
                    'Členská předplatná' => 'member-subscriptions',
                    'Typy předplatného' => 'subscription-types',
                ],
            ],

            'manage-settings' => [
                'intro' => 'Nastavení, která se mění bez nasazení: škála hodnocení, tlačítko v patičce, zámek u „Nejlépe hodnocených dívek", platby a simulovaný online stav.',
                'cannot' => [
                    'Vypnout platební bránu — ta se řídí klíči v prostředí. Volba u plateb jen říká, co dělat, když brána chybí.',
                ],
                'links' => [
                    'Typy předplatného' => 'subscription-types',
                    'Věková brána' => 'manage-age-gate',
                ],
            ],

            'manage-age-gate' => [
                'intro' => 'Věková brána 18+. Právní text, takže se mění tady, ne v kódu.',
                'can' => [
                    'Bránu vypnout — pak se na web vůbec nevykreslí.',
                    'Přepsat text ve všech jazycích a určit, kam vede „Odejít".',
                ],
                'cannot' => [
                    'Změnit text tak, aby si ho návštěvníci nemuseli odsouhlasit znovu — po úpravě se souhlas vyžádá znovu, protože je vázaný na znění.',
                ],
            ],

            'manage-footer' => [
                'intro' => 'Texty v patičce po jazycích, v pořadí, v jakém je patička vykresluje.',
                'links' => [
                    'Menu v patičce' => 'footer-menu-items',
                    'Stránky' => 'pages',
                ],
            ],

            'footer-menu-items' => [
                'intro' => 'Odkazy v patičce a jejich rozdělení do sloupců. „Publikum" řídí, komu se odkaz ukáže — nepřihlášeným, ženám, mužům, nebo všem.',
                'links' => [
                    'Texty v patičce' => 'manage-footer',
                    'Stránky' => 'pages',
                ],
            ],
        ];
    }

    /**
     * Legenda pro danou cestu v panelu, nebo null.
     *
     * @return array{intro: string, can?: array<int, string>, cannot?: array<int, string>, links?: array<string, string>}|null
     */
    public static function for(string $path): ?array
    {
        return self::all()[self::key($path)] ?? null;
    }

    /**
     * Klíč sekce z celé cesty.
     *
     * `/admin/profiles/12/edit` i `/admin/profiles` patří k `profiles`, protože
     * legenda popisuje sekci, ne jednu obrazovku v ní.
     */
    public static function key(string $path): string
    {
        $path = trim($path, '/');
        $prefix = trim(config('filament.path', 'admin'), '/');

        if ($prefix !== '' && str_starts_with($path, $prefix)) {
            $path = trim(substr($path, strlen($prefix)), '/');
        }

        if ($path === '') {
            return '';
        }

        return explode('/', $path)[0];
    }
}
