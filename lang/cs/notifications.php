<?php

return [
    // Profile notifications (for users)
    'profile' => [
        'approved_title' => 'Profil schválen',
        'approved_message' => 'Gratulujeme! Váš profil byl schválen a je nyní viditelný pro ostatní.',
        
        'rejected_title' => 'Profil zamítnut',
        'rejected_message' => 'Váš profil byl zamítnut. Prosím zkontrolujte a aktualizujte svůj profil podle našich pravidel.',
        
        'verified_title' => 'Profil ověřen',
        'verified_message' => 'Váš profil byl ověřen! Nyní máte ověřovací odznak.',
        
        'unverified_title' => 'Ověření odebráno',
        'unverified_message' => 'Ověření vašeho profilu bylo odebráno.',
    ],

    // Rating notifications (for users)
    'rating' => [
        'received_title' => 'Nové hodnocení',
        'received_message' => 'Někdo ohodnotil váš profil :stars hvězdičkami.',
    ],

    // Subscription notifications (for users)
    'subscription' => [
        'created_title' => 'Předplatné aktivováno',
        'created_message' => 'Vaše předplatné :type je aktivní do :ends_at.',
        
        'renewed_title' => 'Předplatné obnoveno',
        'renewed_message' => 'Vaše předplatné bylo obnoveno do :ends_at.',
        
        'expired_title' => 'Předplatné vypršelo',
        'expired_message' => 'Vaše předplatné vypršelo. Obnovte ho pro pokračování prémiových funkcí.',
        
        'cancelled_title' => 'Předplatné zrušeno',
        'cancelled_message' => 'Vaše předplatné bylo zrušeno.',
        
        'expiring_soon_title' => 'Předplatné brzy vyprší',
        'expiring_soon_message' => 'Vaše předplatné vyprší za :days dní. Zvažte obnovení.',
    ],

    // Favorite notifications (for profile owners)
    'favorite' => [
        'added_title' => 'Nový oblíbenec',
        'added_message' => 'Někdo přidal váš profil do oblíbených!',
    ],

    // Admin notifications
    'admin' => [
        'new_profile_title' => 'Nový profil k schválení',
        'new_profile_message' => 'Nový profil ":name" čeká na schválení.',
    ],
];
