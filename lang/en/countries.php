<?php

return [
    'form' => [
        'code' => 'Country code (ISO)',
        'code_helper' => 'Two-letter ISO 3166-1 code, e.g. CZ or DE. Must match the code used on profiles and cities.',
        'name_override' => 'Custom name',
        'name_override_helper' => 'Leave empty to use the standard country name. Fill in only when the site should display something else.',
        'sort_order' => 'Order',
        'sort_order_helper' => 'Lower numbers appear first.',
        'visible' => 'Show on the site',
        'visible_helper' => 'A hidden country disappears from the country list and from search. A visible country stays in the list even when it has no profiles.',
    ],
    'table' => [
        'code' => 'Code',
        'name' => 'Country',
        'profiles_count' => 'Profiles',
        'profiles_count_tooltip' => 'Approved, public and verified profiles — exactly what a visitor sees after clicking through.',
        'sort_order' => 'Order',
        'visible' => 'Visible',
        'created' => 'Created',
    ],
];
