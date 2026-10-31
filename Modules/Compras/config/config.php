<?php

return [
    'name' => 'Compras',

    /*
    |--------------------------------------------------------------------------
    | Usuarios con roles y accesos específicos en Compras Generales
    |--------------------------------------------------------------------------
    | Lista de IDs de usuarios con permisos especiales según el área o función.
    */
    'users' => [
        'compras' => array_filter(explode(',', (string) env('USERS_COMPRAS_COMPRAS', '')), 'strlen'),
        'admin'   => array_filter(explode(',', (string) env('USERS_COMPRAS_ADMIN', '')), 'strlen'),
        'adminz'  => array_filter(explode(',', (string) env('USERS_COMPRAS_ADMINZ', '')), 'strlen'),
        'rt'      => array_filter(explode(',', (string) env('USERS_COMPRAS_RT', '')), 'strlen'),
        'autos'   => array_filter(explode(',', (string) env('USERS_COMPRAS_AUTOS', '')), 'strlen'),
        'tg'      => array_filter(explode(',', (string) env('USERS_COMPRAS_TG', '')), 'strlen'),
        'sop_gas' => array_filter(explode(',', (string) env('USERS_SOP_GAS', '')), 'strlen'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Usuarios con roles y accesos específicos en Macrotaller
    |--------------------------------------------------------------------------
    */
    'users_macro' => [
        'compras' => array_filter(explode(',', (string) env('USERS_MACRO_COMPRAS', '')), 'strlen'),
        'admin'   => array_filter(explode(',', (string) env('USERS_MACRO_ADMIN', '')), 'strlen'),
        'adminz'  => array_filter(explode(',', (string) env('USERS_COMPRAS_ADMINZ', '')), 'strlen'),
        'macro'   => array_filter(explode(',', (string) env('USERS_MACRO_MACRO', '')), 'strlen'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Excepciones de negocio
    |--------------------------------------------------------------------------
    */
    'excepciones' => [
        'autos'           => array_filter(explode(',', (string) env('EXCEPCIONES_AUTOS', '')), 'strlen'),
        'macro'           => array_filter(explode(',', (string) env('EXCEPCIONES_MACRO', '')), 'strlen'),
        'intercompania'   => array_filter(explode(',', (string) env('INTER_EXECPCIONES', '')), 'strlen'),
        'reporte_compras' => array_filter(explode(',', (string) env('EXCEPCIONES_REPORTE_COMPRAS', '')), 'strlen'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integración API Tecno GPS
    |--------------------------------------------------------------------------
    */
    'tecno_gps' => [
        'route'   => env('TECNO_GPS_ROUTE', 'https://horus.tecno-gps.com.mx/api/v1/'),
        'api_key' => env('TECNO_GPS_API_KEY', ''),
    ],
];
