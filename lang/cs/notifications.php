<?php

return [
    'types' => [
        'success' => 'Úspěch',
        'info' => 'Informace',
        'warning' => 'Upozornění',
        'danger' => 'Důležité',
        'system' => 'Systémové',
    ],

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

    // Message notifications (for users)
    'message' => [
        'received_title' => 'Nová zpráva',
        'received_message' => ':name vám poslal(a) novou zprávu.',
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

    // Premium membership notifications (for members)
    'membership' => [
        'created_title' => 'Premium členství aktivováno',
        'created_message' => 'Vaše členství :type je aktivní do :ends_at.',

        'renewed_title' => 'Premium členství obnoveno',
        'renewed_message' => 'Vaše členství bylo obnoveno do :ends_at.',

        'expired_title' => 'Premium členství vypršelo',
        'expired_message' => 'Vaše členství vypršelo. Hodnocení dívek je znovu skryté.',

        'cancelled_title' => 'Premium členství zrušeno',
        'cancelled_message' => 'Vaše členství bylo zrušeno.',

        'expiring_soon_title' => 'Premium členství brzy vyprší',
        'expiring_soon_message' => 'Vaše členství vyprší za :days dní.',
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
