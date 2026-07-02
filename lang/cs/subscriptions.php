<?php

return [
    // Navigation
    'navigation' => [
        'types' => 'Typy předplatného',
        'subscriptions' => 'Předplatná',
    ],

    // Model labels
    'type' => [
        'singular' => 'Typ předplatného',
        'plural' => 'Typy předplatného',
    ],
    'subscription' => [
        'singular' => 'Předplatné',
        'plural' => 'Předplatná',
    ],

    // Duration labels
    'duration' => [
        'days' => '{1} :count den|[2,4] :count dny|[5,*] :count dní',
        'weekly' => 'Týdenní (7 dní)',
        'monthly' => 'Měsíční (30 dní)',
        'quarterly' => 'Čtvrtletní (90 dní)',
        'semi_annual' => 'Pololetní (180 dní)',
        'yearly' => 'Roční (365 dní)',
    ],

    // Status labels
    'status' => [
        'active' => 'Aktivní',
        'expired' => 'Vypršelo',
        'cancelled' => 'Zrušeno',
        'pending' => 'Čeká',
    ],

    // Form fields
    'form' => [
        'basic_info' => 'Základní informace',
        'pricing' => 'Cena a doba trvání',
        'appearance' => 'Vzhled',
        'features' => 'Funkce',
        'subscription_details' => 'Detaily předplatného',
        'dates' => 'Data a stav',
        'notes' => 'Poznámky',

        'name' => 'Název',
        'slug' => 'Slug',
        'description' => 'Popis',
        'price' => 'Cena',
        'duration' => 'Doba trvání',
        'color' => 'Barva',
        'icon' => 'Ikona',
        'sort_order' => 'Pořadí',
        'is_active' => 'Aktivní',
        'features_list' => 'Seznam funkcí',
        'feature_key' => 'Funkce',
        'feature_value' => 'Popis',
        'add_feature' => 'Přidat funkci',
        'features_helper' => 'Přidejte páry klíč-hodnota pro funkce předplatného (např. "prioritni_zobrazeni" => "Profil se zobrazuje první ve vyhledávání")',

        'profile' => 'Profil',
        'subscription_type' => 'Typ předplatného',
        'starts_at' => 'Začíná',
        'ends_at' => 'Končí',
        'status' => 'Stav',
        'auto_renew' => 'Automatické obnovení',
        'auto_renew_helper' => 'Automaticky obnovit předplatné po vypršení',
        'cancel_reason' => 'Důvod zrušení',
    ],

    // Table columns
    'table' => [
        'name' => 'Název',
        'slug' => 'Slug',
        'price' => 'Cena',
        'duration' => 'Doba trvání',
        'active' => 'Aktivní',
        'order' => 'Pořadí',
        'active_count' => 'Aktivní předplatná',

        'profile' => 'Profil',
        'type' => 'Typ',
        'starts' => 'Začíná',
        'ends' => 'Končí',
        'status' => 'Stav',
        'auto_renew' => 'Auto-obnovení',
        'days_remaining' => '{0} Vypršelo|{1} Zbývá :count den|[2,4] Zbývají :count dny|[5,*] Zbývá :count dní',
    ],

    // Filters
    'filter' => [
        'active' => 'Aktivní',
        'status' => 'Stav',
        'type' => 'Typ',
        'expiring_soon' => 'Brzy končí (7 dní)',
        'auto_renew' => 'Auto-obnovení',
    ],

    // Actions
    'actions' => [
        'renew' => 'Obnovit',
        'renew_heading' => 'Obnovit předplatné',
        'renew_description' => 'Toto prodlouží předplatné o :days dní (:type). Nové datum ukončení bude vypočítáno od aktuálního data ukončení.',
        'cancel' => 'Zrušit',
        'cancel_heading' => 'Zrušit předplatné',
        'cancel_description' => 'Opravdu chcete zrušit toto předplatné? Předplatné zůstane aktivní do data ukončení.',
        'reactivate' => 'Reaktivovat',
        'bulk_renew' => 'Obnovit vybrané',
        'bulk_cancel' => 'Zrušit vybrané',
    ],

    // Notifications
    'notifications' => [
        'renewed' => 'Předplatné obnoveno',
        'renewed_body' => 'Předplatné bylo prodlouženo do :date',
        'cancelled' => 'Předplatné zrušeno',
        'reactivated' => 'Předplatné reaktivováno',
        'bulk_renewed' => 'Obnoveno :count předplatných',
        'bulk_cancelled' => 'Zrušeno :count předplatných',
    ],

    // View page
    'view' => [
        'subscription_info' => 'Informace o předplatném',
        'days_remaining' => 'Zbývající dny',
        'activity_log' => 'Historie aktivit',
    ],

    // Log actions
    'log' => [
        'action' => 'Akce',
        'by' => 'Provedl',
        'date' => 'Datum',
        'details' => 'Detaily',
        'system' => 'Systém',

        'created' => 'Vytvořeno',
        'renewed' => 'Obnoveno',
        'cancelled' => 'Zrušeno',
        'expired' => 'Vypršelo',
        'updated' => 'Aktualizováno',
        'reactivated' => 'Reaktivováno',
    ],

    // Stats widget
    'stats' => [
        'active' => 'Aktivní předplatná',
        'active_description' => 'Aktuálně aktivní',
        'expiring_soon' => 'Brzy končí',
        'expiring_description' => 'Do 7 dnů',
        'new_this_month' => 'Nové tento měsíc',
        'new_description' => 'Vytvořeno tento měsíc',
        'expired_this_month' => 'Vypršelo tento měsíc',
        'expired_description' => 'Vypršelo tento měsíc',
    ],
];
