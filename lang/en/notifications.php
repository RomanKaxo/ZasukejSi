<?php

return [
    // Profile notifications (for users)
    'profile' => [
        'approved_title' => 'Profile Approved',
        'approved_message' => 'Congratulations! Your profile has been approved and is now visible to others.',
        
        'rejected_title' => 'Profile Rejected',
        'rejected_message' => 'Your profile has been rejected. Please review and update your profile according to our guidelines.',
        
        'verified_title' => 'Profile Verified',
        'verified_message' => 'Your profile has been verified! You now have a verified badge.',
        
        'unverified_title' => 'Verification Removed',
        'unverified_message' => 'Your profile verification has been removed.',
    ],

    // Rating notifications (for users)
    'rating' => [
        'received_title' => 'New Rating Received',
        'received_message' => 'Someone rated your profile with :stars stars.',
    ],

    // Subscription notifications (for users)
    'subscription' => [
        'created_title' => 'Subscription Activated',
        'created_message' => 'Your :type subscription is now active until :ends_at.',
        
        'renewed_title' => 'Subscription Renewed',
        'renewed_message' => 'Your subscription has been renewed until :ends_at.',
        
        'expired_title' => 'Subscription Expired',
        'expired_message' => 'Your subscription has expired. Renew to continue enjoying premium features.',
        
        'cancelled_title' => 'Subscription Cancelled',
        'cancelled_message' => 'Your subscription has been cancelled.',
        
        'expiring_soon_title' => 'Subscription Expiring Soon',
        'expiring_soon_message' => 'Your subscription will expire in :days days. Consider renewing.',
    ],

    // Favorite notifications (for profile owners)
    'favorite' => [
        'added_title' => 'New Favorite',
        'added_message' => 'Someone added your profile to their favorites!',
    ],

    // Admin notifications
    'admin' => [
        'new_profile_title' => 'New Profile Submission',
        'new_profile_message' => 'A new profile ":name" is pending approval.',
    ],
];
