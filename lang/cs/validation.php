<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validační jazykové řetězce
    |--------------------------------------------------------------------------
    |
    | Následující řádky obsahují výchozí chybové hlášky validátoru. Některá
    | pravidla mají více variant podle typu ověřované hodnoty (pole, soubor,
    | číslo, řetězec).
    |
    */

    'accepted' => 'Pole :attribute musí být přijato.',
    'accepted_if' => 'Pole :attribute musí být přijato, pokud je :other rovno :value.',
    'active_url' => 'Pole :attribute musí obsahovat platnou URL adresu.',
    'after' => 'Pole :attribute musí obsahovat datum po :date.',
    'after_or_equal' => 'Pole :attribute musí obsahovat datum :date nebo pozdější.',
    'alpha' => 'Pole :attribute může obsahovat pouze písmena.',
    'alpha_dash' => 'Pole :attribute může obsahovat pouze písmena, číslice, pomlčky a podtržítka.',
    'alpha_num' => 'Pole :attribute může obsahovat pouze písmena a číslice.',
    'array' => 'Pole :attribute musí být pole.',
    'ascii' => 'Pole :attribute může obsahovat pouze jednobajtové alfanumerické znaky a symboly.',
    'before' => 'Pole :attribute musí obsahovat datum před :date.',
    'before_or_equal' => 'Pole :attribute musí obsahovat datum :date nebo dřívější.',
    'between' => [
        'array' => 'Pole :attribute musí obsahovat :min až :max položek.',
        'file' => 'Soubor :attribute musí mít velikost :min až :max kilobajtů.',
        'numeric' => 'Pole :attribute musí být mezi :min a :max.',
        'string' => 'Pole :attribute musí mít délku :min až :max znaků.',
    ],
    'boolean' => 'Pole :attribute musí být true nebo false.',
    'can' => 'Pole :attribute obsahuje nepovolenou hodnotu.',
    'confirmed' => 'Potvrzení pole :attribute nesouhlasí.',
    'contains' => 'V poli :attribute chybí požadovaná hodnota.',
    'current_password' => 'Zadané heslo je nesprávné.',
    'date' => 'Pole :attribute musí obsahovat platné datum.',
    'date_equals' => 'Pole :attribute musí obsahovat datum shodné s :date.',
    'date_format' => 'Pole :attribute neodpovídá formátu :format.',
    'decimal' => 'Pole :attribute musí mít :decimal desetinných míst.',
    'declined' => 'Pole :attribute musí být odmítnuto.',
    'declined_if' => 'Pole :attribute musí být odmítnuto, pokud je :other rovno :value.',
    'different' => 'Pole :attribute a :other se musí lišit.',
    'digits' => 'Pole :attribute musí obsahovat :digits číslic.',
    'digits_between' => 'Pole :attribute musí obsahovat :min až :max číslic.',
    'dimensions' => 'Obrázek :attribute má neplatné rozměry.',
    'distinct' => 'Pole :attribute obsahuje duplicitní hodnotu.',
    'doesnt_end_with' => 'Pole :attribute nesmí končit na jednu z hodnot: :values.',
    'doesnt_start_with' => 'Pole :attribute nesmí začínat na jednu z hodnot: :values.',
    'email' => 'Pole :attribute musí obsahovat platnou e-mailovou adresu.',
    'ends_with' => 'Pole :attribute musí končit na jednu z hodnot: :values.',
    'enum' => 'Zvolená hodnota pole :attribute je neplatná.',
    'exists' => 'Zvolená hodnota pole :attribute je neplatná.',
    'extensions' => 'Pole :attribute musí mít jednu z těchto přípon: :values.',
    'file' => 'Pole :attribute musí obsahovat soubor.',
    'filled' => 'Pole :attribute musí být vyplněno.',
    'gt' => [
        'array' => 'Pole :attribute musí obsahovat více než :value položek.',
        'file' => 'Soubor :attribute musí být větší než :value kilobajtů.',
        'numeric' => 'Pole :attribute musí být větší než :value.',
        'string' => 'Pole :attribute musí být delší než :value znaků.',
    ],
    'gte' => [
        'array' => 'Pole :attribute musí obsahovat alespoň :value položek.',
        'file' => 'Soubor :attribute musí být alespoň :value kilobajtů.',
        'numeric' => 'Pole :attribute musí být větší nebo rovno :value.',
        'string' => 'Pole :attribute musí mít alespoň :value znaků.',
    ],
    'hex_color' => 'Pole :attribute musí obsahovat platnou hexadecimální barvu.',
    'image' => 'Pole :attribute musí obsahovat obrázek.',
    'in' => 'Zvolená hodnota pole :attribute je neplatná.',
    'in_array' => 'Pole :attribute se musí nacházet v :other.',
    'integer' => 'Pole :attribute musí být celé číslo.',
    'ip' => 'Pole :attribute musí obsahovat platnou IP adresu.',
    'ipv4' => 'Pole :attribute musí obsahovat platnou IPv4 adresu.',
    'ipv6' => 'Pole :attribute musí obsahovat platnou IPv6 adresu.',
    'json' => 'Pole :attribute musí obsahovat platný JSON řetězec.',
    'list' => 'Pole :attribute musí být seznam.',
    'lowercase' => 'Pole :attribute musí být psáno malými písmeny.',
    'lt' => [
        'array' => 'Pole :attribute musí obsahovat méně než :value položek.',
        'file' => 'Soubor :attribute musí být menší než :value kilobajtů.',
        'numeric' => 'Pole :attribute musí být menší než :value.',
        'string' => 'Pole :attribute musí být kratší než :value znaků.',
    ],
    'lte' => [
        'array' => 'Pole :attribute nesmí obsahovat více než :value položek.',
        'file' => 'Soubor :attribute nesmí být větší než :value kilobajtů.',
        'numeric' => 'Pole :attribute musí být menší nebo rovno :value.',
        'string' => 'Pole :attribute nesmí být delší než :value znaků.',
    ],
    'mac_address' => 'Pole :attribute musí obsahovat platnou MAC adresu.',
    'max' => [
        'array' => 'Pole :attribute nesmí obsahovat více než :max položek.',
        'file' => 'Soubor :attribute nesmí být větší než :max kilobajtů.',
        'numeric' => 'Pole :attribute nesmí být větší než :max.',
        'string' => 'Pole :attribute nesmí být delší než :max znaků.',
    ],
    'max_digits' => 'Pole :attribute nesmí obsahovat více než :max číslic.',
    'mimes' => 'Pole :attribute musí obsahovat soubor typu: :values.',
    'mimetypes' => 'Pole :attribute musí obsahovat soubor typu: :values.',
    'min' => [
        'array' => 'Pole :attribute musí obsahovat alespoň :min položek.',
        'file' => 'Soubor :attribute musí mít alespoň :min kilobajtů.',
        'numeric' => 'Pole :attribute musí být alespoň :min.',
        'string' => 'Pole :attribute musí mít alespoň :min znaků.',
    ],
    'min_digits' => 'Pole :attribute musí obsahovat alespoň :min číslic.',
    'missing' => 'Pole :attribute musí chybět.',
    'missing_if' => 'Pole :attribute musí chybět, pokud je :other rovno :value.',
    'missing_unless' => 'Pole :attribute musí chybět, pokud :other není :value.',
    'missing_with' => 'Pole :attribute musí chybět, pokud je vyplněno :values.',
    'missing_with_all' => 'Pole :attribute musí chybět, pokud jsou vyplněny :values.',
    'multiple_of' => 'Pole :attribute musí být násobkem :value.',
    'not_in' => 'Zvolená hodnota pole :attribute je neplatná.',
    'not_regex' => 'Formát pole :attribute je neplatný.',
    'numeric' => 'Pole :attribute musí být číslo.',
    'password' => [
        'letters' => 'Pole :attribute musí obsahovat alespoň jedno písmeno.',
        'mixed' => 'Pole :attribute musí obsahovat alespoň jedno velké a jedno malé písmeno.',
        'numbers' => 'Pole :attribute musí obsahovat alespoň jednu číslici.',
        'symbols' => 'Pole :attribute musí obsahovat alespoň jeden speciální znak.',
        'uncompromised' => 'Zadaná hodnota pole :attribute se objevila v úniku dat. Zvolte prosím jinou.',
    ],
    'present' => 'Pole :attribute musí být přítomno.',
    'present_if' => 'Pole :attribute musí být přítomno, pokud je :other rovno :value.',
    'present_unless' => 'Pole :attribute musí být přítomno, pokud :other není :value.',
    'present_with' => 'Pole :attribute musí být přítomno, pokud je vyplněno :values.',
    'present_with_all' => 'Pole :attribute musí být přítomno, pokud jsou vyplněny :values.',
    'prohibited' => 'Pole :attribute je zakázáno.',
    'prohibited_if' => 'Pole :attribute je zakázáno, pokud je :other rovno :value.',
    'prohibited_if_accepted' => 'Pole :attribute je zakázáno, pokud je :other přijato.',
    'prohibited_if_declined' => 'Pole :attribute je zakázáno, pokud je :other odmítnuto.',
    'prohibited_unless' => 'Pole :attribute je zakázáno, pokud :other není v :values.',
    'prohibits' => 'Pole :attribute zakazuje přítomnost :other.',
    'regex' => 'Formát pole :attribute je neplatný.',
    'required' => 'Pole :attribute je povinné.',
    'required_array_keys' => 'Pole :attribute musí obsahovat záznamy pro: :values.',
    'required_if' => 'Pole :attribute je povinné, pokud je :other rovno :value.',
    'required_if_accepted' => 'Pole :attribute je povinné, pokud je :other přijato.',
    'required_if_declined' => 'Pole :attribute je povinné, pokud je :other odmítnuto.',
    'required_unless' => 'Pole :attribute je povinné, pokud :other není v :values.',
    'required_with' => 'Pole :attribute je povinné, pokud je vyplněno :values.',
    'required_with_all' => 'Pole :attribute je povinné, pokud jsou vyplněny :values.',
    'required_without' => 'Pole :attribute je povinné, pokud není vyplněno :values.',
    'required_without_all' => 'Pole :attribute je povinné, pokud nejsou vyplněny žádné z :values.',
    'same' => 'Pole :attribute a :other se musí shodovat.',
    'size' => [
        'array' => 'Pole :attribute musí obsahovat :size položek.',
        'file' => 'Soubor :attribute musí mít velikost :size kilobajtů.',
        'numeric' => 'Pole :attribute musí být rovno :size.',
        'string' => 'Pole :attribute musí mít délku :size znaků.',
    ],
    'starts_with' => 'Pole :attribute musí začínat na jednu z hodnot: :values.',
    'string' => 'Pole :attribute musí být řetězec.',
    'timezone' => 'Pole :attribute musí obsahovat platnou časovou zónu.',
    'unique' => 'Hodnota pole :attribute je již obsazena.',
    'uploaded' => 'Nahrání souboru :attribute se nezdařilo.',
    'uppercase' => 'Pole :attribute musí být psáno velkými písmeny.',
    'url' => 'Pole :attribute musí obsahovat platnou URL adresu.',
    'ulid' => 'Pole :attribute musí obsahovat platný ULID.',
    'uuid' => 'Pole :attribute musí obsahovat platný UUID.',

    /*
    |--------------------------------------------------------------------------
    | Vlastní validační hlášky
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'email' => [
            'unique' => 'Tato e-mailová adresa je již zaregistrována. Použijte prosím jinou adresu nebo se přihlaste.',
        ],
        'phone' => [
            'unique' => 'Toto telefonní číslo je již zaregistrováno. Použijte prosím jiné číslo.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Vlastní názvy atributů
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'jméno a příjmení',
        'email' => 'e-mailová adresa',
        'phone' => 'telefonní číslo',
        'password' => 'heslo',
        'password_confirmation' => 'potvrzení hesla',
        'current_password' => 'současné heslo',
    ],

];
