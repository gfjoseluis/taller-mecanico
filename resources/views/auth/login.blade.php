<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Entrar — Taller Alto Tonelaje</title><link rel="stylesheet" href="/css/app.css"></head>
<body class="bg-[#1B1E24] flex items-center justify-center min-h-screen p-4">
<div class="w-full max-w-3xl grid md:grid-cols-2 rounded overflow-hidden shadow-2xl">
<div class="bg-[#1B1E24] text-white p-8 flex flex-col justify-between">
<div><p class="font-display text-5xl font-bold leading-none">TALLER<br><span class="text-[#F5B301]">ALTO TONELAJE</span></p><p class="mt-4 text-sm text-gray-300">Órdenes, inventario, dinero y contabilidad. Una placa por orden, todo cuadra.</p></div>
<div><div class="cinta h-2 rounded-sm"></div><p class="text-xs text-gray-400 mt-2">Servicio · Inventario · Tesorería</p></div>
</div>
<form method="POST" action="/login" class="bg-white p-8">@csrf
<h1 class="font-display text-3xl font-semibold text-[#1B1E24]">Entrar al turno</h1>
<p class="text-sm text-gray-500 mb-4">admin@taller.test / admin123</p>
<label class="text-sm font-medium">Correo<input name="email" type="email" value="admin@taller.test" class="border-2 border-[#1B1E24] w-full p-2 rounded mt-1"></label>
<label class="text-sm font-medium block mt-3">Contraseña<input name="password" type="password" value="admin123" class="border-2 border-[#1B1E24] w-full p-2 rounded mt-1"></label>
@error('email')<p class="text-[#B3402A] text-sm mt-2">{{ $message }}</p>@enderror
<button class="bg-[#F5B301] text-[#1B1E24] font-display font-semibold text-xl w-full mt-4 py-2 rounded">Abrir caja</button>
</form></div></body></html>
