# Funkční kontrola a opravy – 13. 10. 2026

Kontrolu provedlo šest auditorů v oddělených lokálních databázích: profil poskytovatele, služby/ceny/statistiky, členská sekce, přihlášení/zprávy/oznámení, veřejný web a administrace. Výchozí verze byla `bb6e909`. Kontrola zahrnovala desktop i šířky 768, 375 a 320 px v Chromium a WebKit.

Administrace měla 80 kontrolovaných GET stránek. Jejich načtení bylo doplněno ukládáním a opětovnou úpravou nastavení, reprezentativním CRUD, hledáním, řazením a stránkováním. U formulářů jsme kontrolovali skutečné databázové zápisy a stav po obnovení stránky. Načtení stránky samo o sobě není důkazem funkčnosti všech jejích tlačítek; jednotlivé auditorské záznamy rozlišují provedené kroky a neověřené varianty.

## Profil, fotografie a administrace

| Původní chyba | Chování po opravě |
|---|---|
| Země a město po uložení vypadaly prázdné. | Formulář převádí uložené ISO označení země na formát výběru; země a město zůstávají viditelné po načtení. |
| Dashboard ukazoval pevně zadané návštěvy, hodnocení a počet recenzí. | Používá skutečná data vlastního profilu; prázdný účet ukazuje nulu a chybějící hodnocení. |
| Ukládací tlačítka a výběry měn přesahovaly 320px obrazovku. | Prvky respektují dostupnou šířku. |
| Více náhledů fotek překrývalo dialog a mazací tlačítka. | Náhledy jsou v běžném toku obsahu a dialog lze posouvat; odebrání i uložení fungují. |
| Hlavní stránka fotografií neukazovala čekající ověření. | Po odeslání i obnovení stránky zobrazuje „Čeká na ověření“. |
| Nesprávné heslo při mazání účtu nemělo viditelnou chybu. | Dialog zůstane otevřený s vysvětlením a účet se nezmění. |
| Ve WebKitu překrývala mazací dialog jeho vlastní vrstva pozadí. | Tlačítka Zrušit a Smazat jsou dostupná. |
| Administrace odmítala uloženou zemi CZ. | Výběr přijímá skutečný formát ISO kódů; profil lze upravit bez opětovné volby země. |
| První telefon se při uložení prázdného seznamu kontaktů vymazal. | První telefon se zapíše do kontaktů; explicitní odstranění existujících kontaktů dál telefon vymaže. |
| Vyhledávání e-mailu u ověřování fotografií vracelo 500. | Vyhledává skutečný vztah profilu a uživatele. |
| Formulář členských předplatných zobrazoval názvy překladových klíčů. | Používá existující popisky. |

## Ceny a předplatné

| Původní chyba | Chování po opravě |
|---|---|
| Cena OutCall bez InCall se ztratila. | Samostatná cena OutCall se uloží a znovu načte u místního i zahraničního ceníku. |
| Další řádek se stejnou délkou přepisoval pevnou cenu. | Duplicitní délka vyvolá viditelnou validační chybu; uložený ceník zůstane zachovaný. Historické duplicity zůstávají editovatelné pro nápravu. |
| Veřejné délky 10/20 hodin se zobrazovaly jako 1/2 hodiny. | Tabulka zachovává celou číselnou délku v češtině i angličtině. |
| Nedostupná platba poskytovatelského předplatného vracela uživatele bez vysvětlení. | Stránka vypíše skutečnou chybu plateb. |
| Parametr `granted=1` mohl falešně potvrdit aktivaci. | Člen i poskytovatel musí mít vlastní skutečně aktivní ručně vytvořený záznam. Ruční aktivace se neoznačuje jako zaplacená. |
| Vypnutá brána mohla použít ponechané přístupové údaje. | Checkout odmítne nedostupnou metodu a neodešle žádost do brány. Existující výslovný režim bez brány zůstává zachovaný. |

## Členská sekce a veřejný web

| Původní chyba | Chování po opravě |
|---|---|
| Členský výběr kraje nic nefiltroval. | Mění nabídku i historii podle katalogu měst a uchovává výběr v URL. Neznámé regiony se nepřisuzují odhadem. |
| Odeslané hlášení profilu chybělo ve vlastní historii. | Nové přihlášené hlášení ukládá stabilní ID oznamovatele a zobrazuje se vedle starších případů. Anonymní hlášení nelze přiřadit podle ručně zadaného e-mailu. Při odeslání se znovu ověřuje dostupnost cílového profilu, aby upravené ID neodhalovalo soukromé profily. |
| Člen bez Premium viděl cizí hodnocení. | Komunitní hodnoty respektují oprávnění v historii i veřejném detailu. Vlastní hodnocení zůstává dostupné. |
| „Ostatní“ zahrnovalo i vlastní hlas. | Průměr obsahuje pouze ostatní hlasy. |
| Filtry členského archivu a hodnocení byly na mobilu oříznuté. | Vejdou se do dostupné šířky. |
| Archiv odkazoval na nepřístupný detail. | Nenabízí nefunkční detail a nezveřejňuje soukromé profily. Nepřístupná média používají neutrální stav místo rozbitého obrázku. |
| Veřejná tlačítka Nové/Doporučení neměnila skutečné pořadí. | Pořadí respektuje zvolená kritéria a používá stabilní doplňkové řazení. |
| Video filtr neviděl videa uložená uploaderem. | Rozpoznává `profile-video` i historická videa v `profile-images`, vždy s video MIME typem. |
| Země/kraj veřejného seznamu se ztrácely po obnovení. | Výběr se přenáší přes URL a obnovuje. |
| Mobilní karty neměly použitelný odkaz. | Obrázek i jméno otevírají správný profil; ovládání galerie zůstává samostatné. |
| Vyhledávací část překrývala země a filtry. | Rozložení je průchozí na mobilu i tabletu. Livewire zachovává veřejnou variantu také po změně filtru. |
| Propagační registrace nereagovala. | Otevírá registrační dialog. |
| Hostovi srdíčko předstíralo uložení oblíbeného profilu. | Vyžádá přihlášení; skutečné ukládání člena zůstává funkční. |
| Neplatné stránkování API vracelo 500. | Vrací JSON 422 s validací; velikost stránky je 1–100. Soukromá pole ani hodnoty hodnocení nejsou v odpovědi API. |

## Přihlášení a oznámení

| Původní chyba | Chování po opravě |
|---|---|
| Opětovně otevřený login/reset dialog měl zakázané tlačítko. | Otevření obnoví stav ovládání. |
| Návrat z obnovy hesla nechal její dialog nad přihlášením. | Původní dialog se zavře a přihlášení je dostupné. |
| Tlačítka obnovy hesla měla bílé písmo na průhledném pozadí. | Paleta je registrovaná v Tailwind a akce jsou viditelné. |
| Přihlašovací tlačítko přesahovalo 320px obrazovku. | Mobilní řazení formuláře je celé dostupné. |
| Archiv globálního oznámení vracel 500. | Používá datum archivace aktuálního uživatele; sdílený záznam se nemění. |
| Mobilní Oznámení vedla jen na archiv. | Aktivní oznámení mají vlastní stránku s přečtením a archivací. |
| Historie člena ukazovala poskytovatelskou navigaci. | Aktivní oznámení i archiv používají navigaci podle účtu. Trvalé smazání je nabízeno pouze pro osobní oznámení. |

## Soukromá média a nasazení

Přímé odkazy původně vydávaly fotografie neveřejných profilů. Doručování nyní kontroluje schválený veřejný profil, vlastníka nebo administrátora a používá `private, no-store`. Nové profilové originály, videa a konverze jsou mimo veřejný disk. CMS obrázky zachovávají veřejné doručování.

Staré veřejné soubory je nutné fyzicky přesunout; samotná změna controlleru nestačí. Příkaz ověřuje kopie a metadata před odstraněním veřejných originálů, podporuje opakování a hlásí nedokončený přesun. Lokálně byly po záloze koordinovaně přesunuty originální databáze i šest testovacích kopií a ověřeny přímé HTTP odkazy. Produkční server ani CDN nebyly v této úloze nasazeny nebo vyčištěny.

`deploy.sh` spouští přesun a při jeho selhání ponechá aplikaci v maintenance mode. Pro sdílená úložiště, zastavení konverzních workerů a CDN platí [postup migrace médií](profile-media-privacy.md).

## Ověření a hranice

Finální společný běh prošel **894 testů / 2 379 assertions**, bez failures, errors nebo skipped testů. Zůstává devět existujících PHPUnit deprecations. První společný běh měl 886 testů; po doplnění kontroly cíle hlášení a izolace privátního testovacího disku byl spuštěn celý finální běh znovu.

Cílené kontrolní matice po opravách: 66 autentizačních/oznamovacích, 36 členských, 48 veřejných a dalších osm čerstvých krajových interakcí. Tyto kroky prošly v Chromium i WebKit na uvedených šířkách. Rodičovská kontrola navíc znovu provedla 14 scénářů profilu/fotografií/mazání, 12 cenových kontrol, čtyři administrátorské, osm kontrol nedostupné platby a čtyři kombinace veřejného ceníku CS/EN.

Ověření proběhlo v pěti navazujících krocích:

1. Šest oddělených auditů a konkrétní reprodukce chyb.
2. Regresní testy původních selhání a jejich opravených výsledků.
3. Skutečné formuláře, přepsání, databázové zápisy a obnovení stránky na desktopu i mobilu.
4. Kontroly oprávnění, přímých mediálních URL, přesunu souborů a zachování SHA256 původních dat.
5. Společný build, kompletní PHP sada, syntax, kompilace config/route/view cache a závěrečná kontrola diffu.

Frontend build prošel (91 modulů). Config, route a view cache se úspěšně vytvořily a následně byly vyčištěny pro běžný lokální vývoj. PHP syntax změněných souborů i syntax deploy skriptů prošla. Původních 36 souborů médií/CMS a konverzí zůstalo zachováno byte-for-byte; v sedmi kontrolovaných databázích nejsou profilová média na veřejném disku. Testovací disk `profile-media` je oddělený od skutečných lokálních souborů.

Kontrolované části zahrnují uložení a přepsání formulářů, opětovné načtení, služby a jazyky, ceny/dostupnost, hesla, registraci a obnovu, lokální zprávy, osobní i globální oznámení, oblíbené profily, hlášení, CMS, veřejné filtry, API a administraci.

Reálné platby, SMTP doručení, externí telefonní aplikace a produkční provoz nebyly ověřovány. Některé dynamické popupy a přehrávání videa v mobilním WebKitu nebylo možné dokončit kvůli pádu lokálního WPE runneru; to není vydáváno za prokázanou chybu aplikace. Fyzický dotykový swipe a všechny kombinace dat/stavů nejsou pokryté. Existující PHPUnit deprecations a buildové informace o absolutních SVG cestách jsou oddělené od nových funkčních chyb.

## Doplnění kontroly nastavení účtu

Původní audit nesprávně přijal zamčený e-mail v hlavních nastaveních jako záměr. Formuláře člena i poskytovatele nyní mají dostupnou změnu adresy s potvrzením současným heslem. Nová adresa se ukládá, vyžaduje ověření a následně funguje pro přihlášení. Původní přihlašovací adresa ani původní ověřovací odkaz už nefungují. Stejná pravidla a odeslání ověřovacího odkazu platí pro starší `/account/profile` formulář.

Ve stejném nastavení byla opravena ztráta uloženého telefonu při změně přezdívky: pole, které formulář neodesílá, se nemaže. U nezměněné adresy není požadováno heslo ani nové ověření. Potvrzovací heslo se při chybě validace neukládá do session starých vstupů. Chybové a ukládací bannery respektují šířku mobilu.

Funkční kontrakt běžného nastavení se kontroluje skutečným postupem **změnit → uložit → ověřit zápis → načíst znovu → přepsat → načíst znovu**. U přihlašovacích údajů se doplňuje odhlášení, přihlášení novými údaji a odmítnutí starých. Disabled/readonly není samo o sobě důkazem zamýšleného chování; audit musí ověřit očekávanou funkci a dostupnou cestu změny. Základní pole e-mail, přezdívka, telefon a heslo se z kontroly nevynechávají.

Regresní sada změny e-mailu má 20 testů / 121 assertions. Původní chyby jsou doložené selháním sedmi základních, čtyř legacy a jednoho session testu před příslušnými opravami. Browser provedl 96 kontrol hlavních formulářů v Chromium/WebKit na 1440/375/320 px a osm kompletních legacy průchodů na desktopu a mobilu. Kontrolovány jsou duplicity, špatné heslo, databázové zápisy, ověření, oba přihlašovací e-maily, přepsání přezdívky a zachování telefonu. Skutečné SMTP doručení je nadále mimo rozsah; místní browser používá log mailer, testy zachycují skutečnou verifikační notifikaci.

Po tomto doplnění prošla znovu celá sada: **914 testů / 2 501 assertions**, bez failures/errors/skipped, s devíti existujícími PHPUnit deprecations. Nový build, syntax a config/route/view cache rovněž prošly.
