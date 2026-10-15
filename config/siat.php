<?php

return [
    // Proveedor gateway configurable desde menú Configuración (tabla settings con fallback .env)
    'driver' => env('SIAT_DRIVER', 'mock'), // mock | http
    'base_url' => env('SIAT_GATEWAY_URL', ''),
    'token' => env('SIAT_GATEWAY_TOKEN', ''),
    'nit' => env('SIAT_NIT', ''),
    'sucursal' => env('SIAT_SUCURSAL', '0'),
    'punto_venta' => env('SIAT_PUNTO_VENTA', '0'),
    'ambiente' => env('SIAT_AMBIENTE', 'piloto'), // piloto | produccion
    'modalidad' => env('SIAT_MODALIDAD', 'electronica_en_linea'),
    'timeout' => (int) env('SIAT_TIMEOUT', 25),
    'cola_reintentos' => (int) env('SIAT_REINTENTOS', 5),
];
