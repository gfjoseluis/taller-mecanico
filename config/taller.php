<?php

return [
    'empresa' => [
        'nombre' => env('TALLER_EMPRESA', 'Taller Alto Tonelaje'),
        'nit' => env('TALLER_NIT', ''),
        'direccion' => env('TALLER_DIRECCION', ''),
        'telefono' => env('TALLER_TELEFONO', ''),
        'logo_path' => env('TALLER_LOGO', null),
    ],
    'moneda' => env('TALLER_MONEDA', 'BOB'),
    'iva_defecto' => (float) env('TALLER_IVA', 0.13),
    'paginacion' => (int) env('TALLER_PAGINACION', 15),
    'sucursal_defecto' => env('TALLER_SUCURSAL', '0'),
];
