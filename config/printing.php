<?php

return [
    'default' => env('PRINT_DEFAULT', 'pdf'), // pdf | termica | normal
    // Por estación: ['CAJA-01' => ['driver' => 'termica', 'ancho' => 80, ...]]
    'estaciones' => [],
    'termica' => [
        'ancho_mm' => 80,
        'qz_host' => env('QZ_HOST', 'http://localhost:8181'),
    ],
];
