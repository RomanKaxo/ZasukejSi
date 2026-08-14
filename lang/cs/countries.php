<?php

return [
    'form' => [
        'code' => 'Kód země (ISO)',
        'code_helper' => 'Dvoupísmenný kód podle ISO 3166-1, například CZ nebo DE. Musí odpovídat kódu použitému u profilů a měst.',
        'name_override' => 'Vlastní název',
        'name_override_helper' => 'Nechte prázdné pro standardní název země. Vyplňte jen tehdy, když má web zobrazovat něco jiného.',
        'sort_order' => 'Pořadí',
        'sort_order_helper' => 'Nižší číslo se zobrazí výše.',
        'visible' => 'Zobrazit na webu',
        'visible_helper' => 'Skrytá země zmizí ze seznamu zemí i z vyhledávání. Viditelná země zůstane v seznamu i tehdy, když nemá žádné profily.',
    ],
    'table' => [
        'code' => 'Kód',
        'name' => 'Země',
        'profiles_count' => 'Profilů',
        'profiles_count_tooltip' => 'Počet schválených, veřejných a ověřených profilů — přesně to, co uvidí návštěvník po kliknutí.',
        'sort_order' => 'Pořadí',
        'visible' => 'Viditelná',
        'created' => 'Vytvořeno',
    ],
];
