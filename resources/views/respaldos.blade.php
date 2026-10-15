@extends('layouts.admin')
@section('content')
<h1 class="text-xl font-bold mb-1">Respaldos (copias de seguridad)</h1>
<p class="text-xs text-gray-500 mb-3">Diaria automática 2:00 am + manual. Restaurar pide escribir RESTAURAR + tu clave y guarda copia previa. Si el último tiene +24h, créalo ya.</p>
@if(session('ok'))<p class="bg-green-100 p-2 rounded mb-2">{{ session('ok') }}</p>@endif
@if($errors->any())<p class="bg-red-100 p-2 rounded mb-2">{{ $errors->first() }}</p>@endif
<form method="POST" action="/respaldos">@csrf<button class="bg-slate-900 text-white px-4 py-2 rounded text-sm">+ Crear respaldo ahora</button></form>
<div class="bg-white p-4 rounded shadow mt-3"><table class="w-full text-sm"><thead><tr class="text-left border-b"><th>Archivo</th><th>Fecha</th><th>Tamaño</th><th></th></tr></thead><tbody>@foreach($items as $r)<tr class="border-b"><td class="font-mono">{{ $r['nombre'] }}</td><td>{{ $r['fecha'] }}</td><td>{{ number_format($r['tamano'] / 1048576, 2) }} MB</td><td class="flex gap-2 flex-wrap"><a href="/respaldos/{{ $r['nombre'] }}/descargar" class="underline">Descargar</a><form method="POST" action="/respaldos/{{ $r['nombre'] }}/restaurar" class="flex gap-1 items-center" onsubmit="return confirm('Se pondrá en mantenimiento 1 minuto. ¿Seguro?')">@csrf<input name="confirmacion" placeholder="Escribe RESTAURAR" class="border p-1 rounded text-xs w-32"><input name="password" type="password" placeholder="Tu clave" class="border p-1 rounded text-xs w-28"><button class="underline text-oxido">Restaurar</button></form><form method="POST" action="/respaldos/{{ $r['nombre'] }}/borrar">@csrf @method('DELETE')<button class="underline text-red-600">Borrar</button></form></td></tr>@endforeach</tbody></table></div>
@endsection
