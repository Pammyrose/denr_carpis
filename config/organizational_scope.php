<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Office hierarchy
    |--------------------------------------------------------------------------
    |
    | An Admin account inherits its office from the employee record with the
    | same email address. PENRO scopes include their subordinate CENRO offices.
    | Super Admin accounts are not restricted by this map.
    |
    */
    'offices' => [
        'Regional Office' => [
            'assignments' => ['Regional Office'],
            'org_codes' => ['ORD', 'FD', 'AD', 'LD', 'CDD', 'SMD', 'LPDD', 'ED', 'PMD'],
            'position_descriptions' => [
                'OFFICE OF THE REGIONAL DIRECTOR',
                'FINANCE DIVISION',
                'ADMINISTRATIVE DIVISION',
                'LEGAL DIVISION',
                'CONSERVATION AND DEVELOPMENT DIVISION',
                'SURVEYS AND MAPPING DIVISION',
                'LICENSES, PATENTS AND DEEDS DIVISION',
                'ENFORCEMENT DIVISION',
                'PLANNING AND MANAGEMENT DIVISION',
            ],
        ],
        'PENRO Abra' => [
            'assignments' => ['PENRO Abra', 'CENRO Bangued', 'CENRO Lagangilang'],
            'org_codes' => ['PENRO_ABRA', 'MSD_ABRA', 'TSD_ABRA', 'CENRO_BANGUED', 'CENRO_LAGANGILANG'],
        ],
        'PENRO Apayao' => [
            'assignments' => ['PENRO Apayao', 'CENRO Conner', 'CENRO Calanasan'],
            'org_codes' => ['PENRO_APAYAO', 'MSD_APAYAO', 'TSD_APAYAO', 'CENRO_CONNER', 'CENRO_CALANASAN'],
        ],
        'PENRO Benguet' => [
            'assignments' => ['PENRO Benguet', 'CENRO Baguio', 'CENRO Buguias'],
            'org_codes' => ['PENRO_BENGUET', 'MSD_BENGUET', 'TSD_BENGUET', 'CENRO_BAGUIO', 'CENRO_BUGUIAS'],
        ],
        'PENRO Ifugao' => [
            'assignments' => ['PENRO Ifugao', 'CENRO Alfonso Lista', 'CENRO Lamut'],
            'org_codes' => ['PENRO_IFUGAO', 'MSD_IFUGAO', 'TSD_IFUGAO', 'CENRO_ALFONSO_LISTA', 'CENRO_LAMUT'],
        ],
        'PENRO Kalinga' => [
            'assignments' => ['PENRO Kalinga', 'CENRO Pinukpuk', 'CENRO Tabuk'],
            'org_codes' => ['PENRO_KALINGA', 'MSD_KALINGA', 'TSD_KALINGA', 'CENRO_PINUKPUK', 'CENRO_TABUK'],
        ],
        'PENRO MT.Province' => [
            'assignments' => ['PENRO MT.Province', 'CENRO Paracelis', 'CENRO Sabangan'],
            'org_codes' => ['PENRO_MTPROV', 'MSD_MTPROV', 'TSD_MTPROV', 'CENRO_PARACELIS', 'CENRO_SABANGAN'],
            'position_descriptions' => ['PENRO MT.Province', 'PENRO Mountain Province', 'CENRO MT.Province', 'CENRO Paracelis', 'CENRO Sabangan'],
        ],
    ],
];
