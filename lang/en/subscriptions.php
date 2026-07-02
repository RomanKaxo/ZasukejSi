<?php

return [
    // Navigation
    'navigation' => [
        'types' => 'Subscription Types',
        'subscriptions' => 'Subscriptions',
    ],

    // Model labels
    'type' => [
        'singular' => 'Subscription Type',
        'plural' => 'Subscription Types',
    ],
    'subscription' => [
        'singular' => 'Subscription',
        'plural' => 'Subscriptions',
    ],

    // Duration labels
    'duration' => [
        'days' => '{1} :count day|[2,*] :count days',
        'weekly' => 'Weekly (7 days)',
        'monthly' => 'Monthly (30 days)',
        'quarterly' => 'Quarterly (90 days)',
        'semi_annual' => 'Semi-Annual (180 days)',
        'yearly' => 'Yearly (365 days)',
    ],

    // Status labels
    'status' => [
        'active' => 'Active',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
        'pending' => 'Pending',
    ],

    // Form fields
    'form' => [
        'basic_info' => 'Basic Information',
        'pricing' => 'Pricing & Duration',
        'appearance' => 'Appearance',
        'features' => 'Features',
        'subscription_details' => 'Subscription Details',
        'dates' => 'Dates & Status',
        'notes' => 'Notes',

        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'price' => 'Price',
        'duration' => 'Duration',
        'color' => 'Color',
        'icon' => 'Icon',
        'sort_order' => 'Sort Order',
        'is_active' => 'Active',
        'features_list' => 'Features List',
        'feature_key' => 'Feature',
        'feature_value' => 'Description',
        'add_feature' => 'Add Feature',
        'features_helper' => 'Add key-value pairs for subscription features (e.g., "priority_listing" => "Profile appears first in search")',

        'profile' => 'Profile',
        'subscription_type' => 'Subscription Type',
        'starts_at' => 'Starts At',
        'ends_at' => 'Ends At',
        'status' => 'Status',
        'auto_renew' => 'Auto-Renew',
        'auto_renew_helper' => 'Automatically renew subscription when it expires',
        'cancel_reason' => 'Cancellation Reason',
    ],

    // Table columns
    'table' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'price' => 'Price',
        'duration' => 'Duration',
        'active' => 'Active',
        'order' => 'Order',
        'active_count' => 'Active Subscriptions',

        'profile' => 'Profile',
        'type' => 'Type',
        'starts' => 'Starts',
        'ends' => 'Ends',
        'status' => 'Status',
        'auto_renew' => 'Auto-Renew',
        'days_remaining' => '{0} Expired|{1} :count day left|[2,*] :count days left',
    ],

    // Filters
    'filter' => [
        'active' => 'Active',
        'status' => 'Status',
        'type' => 'Type',
        'expiring_soon' => 'Expiring Soon (7 days)',
        'auto_renew' => 'Auto-Renew',
    ],

    // Actions
    'actions' => [
        'renew' => 'Renew',
        'renew_heading' => 'Renew Subscription',
        'renew_description' => 'This will extend the subscription by :days days (:type). The new end date will be calculated from the current end date.',
        'cancel' => 'Cancel',
        'cancel_heading' => 'Cancel Subscription',
        'cancel_description' => 'Are you sure you want to cancel this subscription? The subscription will remain active until the end date.',
        'reactivate' => 'Reactivate',
        'bulk_renew' => 'Renew Selected',
        'bulk_cancel' => 'Cancel Selected',
    ],

    // Notifications
    'notifications' => [
        'renewed' => 'Subscription Renewed',
        'renewed_body' => 'Subscription has been extended until :date',
        'cancelled' => 'Subscription Cancelled',
        'reactivated' => 'Subscription Reactivated',
        'bulk_renewed' => ':count subscription(s) renewed',
        'bulk_cancelled' => ':count subscription(s) cancelled',
    ],

    // View page
    'view' => [
        'subscription_info' => 'Subscription Information',
        'days_remaining' => 'Days Remaining',
        'activity_log' => 'Activity Log',
    ],

    // Log actions
    'log' => [
        'action' => 'Action',
        'by' => 'By',
        'date' => 'Date',
        'details' => 'Details',
        'system' => 'System',

        'created' => 'Created',
        'renewed' => 'Renewed',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
        'updated' => 'Updated',
        'reactivated' => 'Reactivated',
    ],

    // Stats widget
    'stats' => [
        'active' => 'Active Subscriptions',
        'active_description' => 'Currently active',
        'expiring_soon' => 'Expiring Soon',
        'expiring_description' => 'Within 7 days',
        'new_this_month' => 'New This Month',
        'new_description' => 'Created this month',
        'expired_this_month' => 'Expired This Month',
        'expired_description' => 'Expired this month',
    ],
];
