<?php

return [
    // Prefijos por tipo de producto. El correlativo es global y atómico (tabla sku_counters).
    'prefijos' => [
        'repuesto' => 'REP',
        'insumo' => 'INS',
        'producto' => 'PRO',
        'servicio' => 'SER',
        'material' => 'MAT',
    ],
    'longitud_correlativo' => 6,
    'separador' => '-',
];
