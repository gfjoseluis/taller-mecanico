<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $title ?? 'Taller' }} - Taller Alto Tonelaje</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {theme: {extend: {
  colors: {grafito: {DEFAULT: '#1B1E24', soft: '#3A4048'}, humo: '#EDEFF2', senal: '#F5B301', oxido: '#B3402A', taller: '#2E7D4F'},
  fontFamily: {display: ['"Barlow Condensed"', 'Arial Narrow', 'sans-serif'], body: ['Barlow', 'system-ui', 'sans-serif']},
}}}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
body {font-family: Barlow, system-ui, sans-serif;}
.font-display, h1, h2 {font-family: "Barlow Condensed", "Arial Narrow", sans-serif; letter-spacing: .01em;}
.num {font-family: "Barlow Condensed", sans-serif; font-variant-numeric: tabular-nums;}
.bg-slate-900 {background-color: #1B1E24 !important;}
.hover\:bg-slate-700:hover {background-color: #3A4048 !important;}
.cinta {background: repeating-linear-gradient(-45deg, #F5B301 0 14px, #1B1E24 14px 28px);}
.nav-act {border-left: 4px solid #F5B301; background: #3A4048;}
:focus-visible {outline: 3px solid #F5B301; outline-offset: 1px;}
</style>
{{-- Alpine lo provee Livewire v3: no cargar CDN aparte (duplicado rompe las acciones) --}}
@livewireStyles
</head>
<body class="bg-humo text-gray-900">
<div class="flex min-h-screen" x-data="{open: true, sub: null, bell: false, user: false}">
<aside :class="open ? 'w-64' : 'w-16'" class="bg-grafito text-white flex-shrink-0 transition-all flex flex-col">
<div class="p-4 flex items-center gap-2">
<button @click="open = !open" class="text-xl" aria-label="Colapsar menú">☰</button>
<span x-show="open" class="font-display font-bold text-xl tracking-wide">TALLER <span class="text-senal">AT</span></span>
</div>
<div class="cinta h-1.5 mx-0"></div>
<nav class="flex-1 px-2 py-2 space-y-1 text-sm">
<a href="/dashboard" class="block px-3 py-2 rounded hover:bg-grafito-soft {{ request()->is('dashboard') ? 'nav-act' : '' }}">📊 <span x-show="open">Tablero</span></a>
@canany(['ots.ver', 'clientes.ver', 'vehiculos.ver'])
<div>
<button @click="sub = sub === 'serv' ? null : 'serv'" class="w-full text-left px-3 py-2 rounded hover:bg-grafito-soft">🛠️ <span x-show="open">Servicio ▾</span></button>
<div x-show="open && sub === 'serv'" class="ml-4 space-y-1">
<a href="/servicios" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('servicios*') ? 'nav-act' : '' }}">Órdenes</a>
<a href="/clientes" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('clientes*') ? 'nav-act' : '' }}">Clientes</a>
<a href="/vehiculos" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('vehiculos*') ? 'nav-act' : '' }}">Vehículos</a>
</div>
</div>
@endcanany
@canany(['productos.ver', 'compras.ver'])
<div>
<button @click="sub = sub === 'inv' ? null : 'inv'" class="w-full text-left px-3 py-2 rounded hover:bg-grafito-soft">📦 <span x-show="open">Inventario ▾</span></button>
<div x-show="open && sub === 'inv'" class="ml-4 space-y-1">
<a href="/productos" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('productos*') ? 'nav-act' : '' }}">Productos</a>
<a href="/categorias" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('categorias*') ? 'nav-act' : '' }}">Categorías</a>
<a href="/compras" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('compras*') ? 'nav-act' : '' }}">Compras</a>
<a href="/proveedores" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('proveedores*') ? 'nav-act' : '' }}">Proveedores</a>
</div>
</div>
@endcanany
@can('ventas.ver')
<div>
<button @click="sub = sub === 'ven' ? null : 'ven'" class="w-full text-left px-3 py-2 rounded hover:bg-grafito-soft">💰 <span x-show="open">Ventas ▾</span></button>
<div x-show="open && sub === 'ven'" class="ml-4 space-y-1">
<a href="/facturas" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('facturas*') ? 'nav-act' : '' }}">Facturas electrónicas</a>
</div>
</div>
@endcan
@can('bancos.ver')
<div>
<button @click="sub = sub === 'tes' ? null : 'tes'" class="w-full text-left px-3 py-2 rounded hover:bg-grafito-soft">🏦 <span x-show="open">Tesorería ▾</span></button>
<div x-show="open && sub === 'tes'" class="ml-4 space-y-1">
<a href="/bancos" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('bancos*') ? 'nav-act' : '' }}">Bancos (4)</a>
<a href="/movimientos" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('movimientos*') ? 'nav-act' : '' }}">Movimientos</a>
<a href="/socios" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('socios*') ? 'nav-act' : '' }}">Socios</a>
<a href="/por-cobrar" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('por-cobrar*') ? 'nav-act' : '' }}">Por cobrar</a>
<a href="/por-pagar" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('por-pagar*') ? 'nav-act' : '' }}">Por pagar</a>
</div>
</div>
@endcan
@can('contabilidad.ver')
<a href="/contabilidad" class="block px-3 py-2 rounded hover:bg-grafito-soft {{ request()->is('contabilidad*') ? 'nav-act' : '' }}">📒 <span x-show="open">Contabilidad</span></a>
@endcan
@can('reportes.ver')
<a href="/reportes" class="block px-3 py-2 rounded hover:bg-grafito-soft {{ request()->is('reportes*') ? 'nav-act' : '' }}">📊 <span x-show="open">Reportes</span></a>
@endcan
@can('config.ver')
<div>
<button @click="sub = sub === 'cfg' ? null : 'cfg'" class="w-full text-left px-3 py-2 rounded hover:bg-grafito-soft">⚙️ <span x-show="open">Config ▾</span></button>
<div x-show="open && sub === 'cfg'" class="ml-4 space-y-1">
<a href="/config" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('config') ? 'nav-act' : '' }}">General</a>
<a href="/respaldos" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('respaldos*') ? 'nav-act' : '' }}">💾 Respaldos</a>
<a href="/log-viewer" class="block px-3 py-1 rounded hover:bg-grafito-soft">📋 Errores</a>
<a href="/usuarios" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('usuarios*') ? 'nav-act' : '' }}">Usuarios</a>
<a href="/roles" class="block px-3 py-1 rounded hover:bg-grafito-soft {{ request()->is('roles*') ? 'nav-act' : '' }}">Roles y permisos</a>
</div>
</div>
@endcan
</nav>
</aside>
<div class="flex-1 flex flex-col min-w-0">
<header class="bg-white shadow px-4 py-2 flex items-center gap-3 sticky top-0 z-30 border-b-4 border-senal">
<span class="font-display font-semibold text-xl text-grafito capitalize">{{ trim(request()->path(), '/') ?: 'tablero' }}</span>
<span class="ml-auto"></span>
<div class="relative">
<button @click="bell = !bell; user = false" aria-label="Notificaciones" class="text-xl px-2">🔔</button>
<span class="absolute -top-1 -right-1 bg-oxido text-white text-[10px] rounded-full px-1">{{ $avisos['conteo'] ?? 0 }}</span>
<div x-show="bell" @click.outside="bell = false" class="absolute right-0 mt-2 w-80 bg-white border rounded shadow p-3 text-sm z-40">
<p class="font-display font-semibold text-lg mb-2">Notificaciones ({{ $avisos['conteo'] ?? 0 }})</p>
@forelse($avisos['lista'] ?? [] as $a)<a href="{{ $a['url'] }}" class="block border-b py-2 hover:bg-humo"><span>{{ $a['icono'] }} <strong>{{ $a['titulo'] }}</strong></span><br><span class="text-xs text-gray-500">{{ $a['detalle'] }}</span></a>@empty<p class="text-gray-500">Sin notificaciones. Aquí avisaremos stock bajo y deudas.</p>@endforelse
</div>
</div>
<div class="relative">
<button @click="user = !user; bell = false" class="flex items-center gap-2 text-sm border-2 border-grafito rounded px-2 py-1"><span class="font-semibold">{{ auth()->user()->name ?? '' }}</span><span class="text-xs text-gray-500">{{ auth()->user()->roles->pluck('name')->join(', ') ?? '' }}</span></button>
<div x-show="user" @click.outside="user = false" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow text-sm z-40">
<form method="POST" action="/logout">@csrf<button class="w-full text-left px-3 py-2 hover:bg-humo">Salir</button></form>
</div>
</div>
</header>
<main class="flex-1 p-4 max-w-7xl w-full">@yield('content')</main>
</div>
</div>
@livewireScripts
</body>
</html>
