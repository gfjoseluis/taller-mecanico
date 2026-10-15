<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $title ?? 'Taller' }} - Taller Alto Tonelaje</title>
<script src="https://cdn.tailwindcss.com"></script>
@livewireStyles
</head>
<body class="bg-gray-100 text-gray-900">
<nav class="bg-slate-900 text-white px-4 py-3 flex gap-4 items-center">
  <a href="/dashboard" class="font-bold">TALLER AT</a>
  <a href="/dashboard" class="hover:underline">Dashboard</a>
  <a href="/clientes" class="hover:underline">Clientes</a>
  <a href="/vehiculos" class="hover:underline">Vehículos</a>
  <a href="/productos" class="hover:underline">Inventario</a>
  <a href="/ots" class="hover:underline">OTs</a>
  <a href="/bancos" class="hover:underline">Bancos</a>
  <a href="/facturas" class="hover:underline">SIAT</a>
  <a href="/config" class="hover:underline">Config</a>
  <span class="ml-auto text-sm">{{ auth()->user()->name ?? '' }}</span>
  <form method="POST" action="/logout">@csrf<button class="text-sm underline">Salir</button></form>
</nav>
<main class="max-w-7xl mx-auto p-4">@yield('content')</main>
@livewireScripts
</body>
</html>
