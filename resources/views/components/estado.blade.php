@props(['texto'])
@php($mapa = [
    'registrada' => ['Abierta', 'background:#dbeafe;color:#1e40af'],
    'vigente' => ['Vigente', 'background:#dbeafe;color:#1e40af'],
    'cerrada-pagada' => ['Cobrada', 'background:#dcfce7;color:#166534'],
    'pagada' => ['Pagada', 'background:#dcfce7;color:#166534'],
    'cerrada-pendiente' => ['Pendiente', 'background:#fef3c7;color:#92400e'],
    'cerrada' => ['Cerrada', 'background:#e5e7eb;color:#374151'],
    'anulada' => ['Anulada', 'background:#fee2e2;color:#991b1b'],
    'pendiente' => ['Pendiente', 'background:#fef3c7;color:#92400e'],
    'aceptada' => ['Aceptada', 'background:#dcfce7;color:#166534'],
    'rechazada' => ['Rechazada', 'background:#fee2e2;color:#991b1b'],
])
@php([$nombre, $estilo] = $mapa[$texto] ?? [$texto, 'background:#e5e7eb;color:#374151'])
<span style="{{ $estilo }};padding:2px 8px;border-radius:9999px;font-size:12px;font-weight:bold;white-space:nowrap">{{ $nombre }}</span>
