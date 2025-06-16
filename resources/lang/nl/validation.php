<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | De standaard foutmeldingen voor validatieregels.
    |
    */

    'accepted'             => 'Het :attribute moet geaccepteerd worden.',
    'active_url'           => 'Het :attribute is geen geldige URL.',
    'after'                => 'Het :attribute moet een datum na :date zijn.',
    'after_or_equal'       => 'Het :attribute moet een datum na of gelijk aan :date zijn.',
    'alpha'                => 'Het :attribute mag alleen letters bevatten.',
    'alpha_dash'           => 'Het :attribute mag alleen letters, cijfers, streepjes en underscores bevatten.',
    'alpha_num'            => 'Het :attribute mag alleen letters en cijfers bevatten.',
    'array'                => 'Het :attribute moet een array zijn.',
    'before'               => 'Het :attribute moet een datum voor :date zijn.',
    'before_or_equal'      => 'Het :attribute moet een datum voor of gelijk aan :date zijn.',
    'between'              => [
        'numeric' => 'Het :attribute moet tussen :min en :max zijn.',
        'file'    => 'Het :attribute moet tussen :min en :max kilobytes zijn.',
        'string'  => 'Het :attribute moet tussen :min en :max tekens zijn.',
        'array'   => 'Het :attribute moet tussen :min en :max items bevatten.',
    ],
    'boolean'              => 'Het :attribute veld moet waar of niet waar zijn.',
    'confirmed'            => 'De bevestiging van :attribute komt niet overeen.',
    'date'                 => 'Het :attribute is geen geldige datum.',
    'date_equals'          => 'Het :attribute moet een datum zijn gelijk aan :date.',
    'date_format'          => 'Het :attribute komt niet overeen met het formaat :format.',
    'different'            => 'Het :attribute en :other moeten verschillend zijn.',
    'digits'               => 'Het :attribute moet :digits cijfers bevatten.',
    'digits_between'       => 'Het :attribute moet tussen :min en :max cijfers bevatten.',
    'dimensions'           => 'Het :attribute heeft geen geldige afmetingen.',
    'distinct'             => 'Het :attribute veld bevat een dubbele waarde.',
    'email'                => 'Het :attribute moet een geldig e-mailadres zijn.',
    'exists'               => 'Het geselecteerde :attribute is ongeldig.',
    'file'                 => 'Het :attribute moet een bestand zijn.',
    'filled'               => 'Het :attribute veld moet een waarde bevatten.',
    'gt'                   => [
        'numeric' => 'Het :attribute moet groter zijn dan :value.',
        'file'    => 'Het :attribute moet groter zijn dan :value kilobytes.',
        'string'  => 'Het :attribute moet meer dan :value tekens bevatten.',
        'array'   => 'Het :attribute moet meer dan :value items bevatten.',
    ],
    'gte'                  => [
        'numeric' => 'Het :attribute moet groter of gelijk zijn aan :value.',
        'file'    => 'Het :attribute moet groter of gelijk zijn aan :value kilobytes.',
        'string'  => 'Het :attribute moet minimaal :value tekens bevatten.',
        'array'   => 'Het :attribute moet minimaal :value items bevatten.',
    ],
    'image'                => 'Het :attribute moet een afbeelding zijn.',
    'in'                   => 'Het geselecteerde :attribute is ongeldig.',
    'in_array'             => 'Het :attribute veld bestaat niet in :other.',
    'integer'              => 'Het :attribute moet een geheel getal zijn.',
    'ip'                   => 'Het :attribute moet een geldig IP-adres zijn.',
    'ipv4'                 => 'Het :attribute moet een geldig IPv4-adres zijn.',
    'ipv6'                 => 'Het :attribute moet een geldig IPv6-adres zijn.',
    'json'                 => 'Het :attribute moet een geldige JSON-string zijn.',
    'lt'                   => [
        'numeric' => 'Het :attribute moet kleiner zijn dan :value.',
        'file'    => 'Het :attribute moet kleiner zijn dan :value kilobytes.',
        'string'  => 'Het :attribute moet minder dan :value tekens bevatten.',
        'array'   => 'Het :attribute moet minder dan :value items bevatten.',
    ],
    'lte'                  => [
        'numeric' => 'Het :attribute moet kleiner of gelijk zijn aan :value.',
        'file'    => 'Het :attribute moet kleiner of gelijk zijn aan :value kilobytes.',
        'string'  => 'Het :attribute moet maximaal :value tekens bevatten.',
        'array'   => 'Het :attribute mag maximaal :value items bevatten.',
    ],
    'max'                  => [
        'numeric' => 'Het :attribute mag niet groter zijn dan :max.',
        'file'    => 'Het :attribute mag niet groter zijn dan :max kilobytes.',
        'string'  => 'Het :attribute mag niet meer dan :max tekens bevatten.',
        'array'   => 'Het :attribute mag niet meer dan :max items bevatten.',
    ],
    'mimes'                => 'Het :attribute moet een bestand zijn van het type: :values.',
    'mimetypes'            => 'Het :attribute moet een bestand zijn van het type: :values.',
    'min'                  => [
        'numeric' => 'Het :attribute moet minimaal :min zijn.',
        'file'    => 'Het :attribute moet minimaal :min kilobytes zijn.',
        'string'  => 'Het :attribute moet minimaal :min tekens bevatten.',
        'array'   => 'Het :attribute moet minimaal :min items bevatten.',
    ],
    'not_in'               => 'Het geselecteerde :attribute is ongeldig.',
    'not_regex'            => 'Het :attribute formaat is ongeldig.',
    'numeric'              => 'Het :attribute moet een getal zijn.',
    'password'             => 'Het wachtwoord is onjuist.',
    'present'              => 'Het :attribute veld moet aanwezig zijn.',
    'regex'                => 'Het :attribute formaat is ongeldig.',
    'required'             => 'Het :attribute veld is verplicht.',
    'required_if'          => 'Het :attribute veld is verplicht wanneer :other gelijk is aan :value.',
    'required_unless'      => 'Het :attribute veld is verplicht tenzij :other zich in :values bevindt.',
    'required_with'        => 'Het :attribute veld is verplicht wanneer :values aanwezig is.',
    'required_with_all'    => 'Het :attribute veld is verplicht wanneer :values aanwezig zijn.',
    'required_without'     => 'Het :attribute veld is verplicht wanneer :values niet aanwezig is.',
    'required_without_all' => 'Het :attribute veld is verplicht wanneer geen van :values aanwezig zijn.',
    'same'                 => 'Het :attribute en :other moeten overeenkomen.',
    'size'                 => [
        'numeric' => 'Het :attribute moet :size zijn.',
        'file'    => 'Het :attribute moet :size kilobytes zijn.',
        'string'  => 'Het :attribute moet :size tekens bevatten.',
        'array'   => 'Het :attribute moet :size items bevatten.',
    ],
    'starts_with'          => 'Het :attribute moet beginnen met een van de volgende: :values.',
    'string'               => 'Het :attribute moet een string zijn.',
    'timezone'             => 'Het :attribute moet een geldige tijdzone zijn.',
    'unique'               => 'Het :attribute is al in gebruik.',
    'uploaded'             => 'Het :attribute kon niet geüpload worden.',
    'url'                  => 'Het :attribute formaat is ongeldig.',
    'uuid'                 => 'Het :attribute moet een geldige UUID zijn.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Hier kan je aangepaste validatieberichten plaatsen voor specifieke attributen.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'aangepast bericht',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Hier kan je "mooie" namen definiëren voor attributen in foutmeldingen.
    |
    */

    'attributes' => [
        'email' => 'e-mailadres',
        'password' => 'wachtwoord',
        'name' => 'naam',
        'username' => 'gebruikersnaam',
        'title' => 'titel',
        'content' => 'inhoud',
        'company' => 'bedrijfsnaam'
        // voeg hier meer toe naar wens
    ],

];
