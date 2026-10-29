<div class="bg-white p-4 rounded shadow w-full">
<div class="flex justify-between items-start mb-2">
<h2 class="font-bold">Orden {{ $orden->numero }} <x-estado :texto="$orden->estado" /></h2>
<x-accion href="/servicios">Volver</x-accion>
</div>
<div class="grid grid-cols-2 gap-2 text-sm mb-3">
<div><p class="text-gray-500 text-xs">Cliente</p><p class="font-bold">{{ $orden->client->nombre ?? '—' }}</p><p class="text-xs">NIT/CI: {{ $orden->client->nit_ci ?? '—' }} · {{ $orden->client->telefono ?? '' }}</p></div>
<div><p class="text-gray-500 text-xs">Vehículo</p><p class="font-bold font-mono">{{ $orden->vehicle->placa ?? '—' }}</p><p class="text-xs">{{ $orden->vehicle->marca ?? '' }} {{ $orden->vehicle->modelo ?? '' }}@if($orden->recepcion?->kilometraje) · Km: {{ number_format($orden->recepcion->kilometraje, 0) }}@endif</p></div>
</div>
<h3 class="font-bold text-sm mt-2">Diagnóstico</h3>
<p class="text-sm whitespace-pre-line">{{ $diagnosticoBase }}</p>
@if(empty($grupos))<p class="text-sm text-gray-500 mt-3">Sin servicios ni repuestos detallados.</p>@else
@foreach($grupos as $g)
<h3 class="font-bold text-sm mt-3">{{ $g['titulo'] }}</h3>
@if($g['clave'] === 'servicio')
<table class="w-full text-sm mt-1"><thead><tr class="text-left border-b"><th>Servicio a realizar</th><th class="text-right">Precio (Bs)</th></tr></thead><tbody>
@foreach($g['lineas'] as $l)<tr class="border-b"><td>{{ $l->etiqueta }}</td><td class="text-right">{{ number_format($l->precio, 2) }}</td></tr>@endforeach
</tbody></table>
@else
<table class="w-full text-sm mt-1"><thead><tr class="text-left border-b"><th>{{ $g['clave'] === 'repuesto' ? 'Repuesto' : 'Insumo' }}</th><th class="text-right">Cantidad</th><th class="text-right">Precio aplicado (Bs)</th><th class="text-right">Subtotal</th></tr></thead><tbody>
@foreach($g['lineas'] as $l)<tr class="border-b"><td>{{ $l->etiqueta }} <span class="text-xs text-gray-500">{{ $l->referencia }}</span></td><td class="text-right">{{ number_format($l->cantidad, 2) }}</td><td class="text-right">{{ number_format($l->precio, 2) }}</td><td class="text-right">{{ number_format($l->cantidad * $l->precio, 2) }}</td></tr>@endforeach
</tbody></table>
@endif
@endforeach
@endif
<div class="bg-gray-50 rounded p-3 mt-3 text-sm">
<p>Mano de obra: <strong>{{ number_format($orden->mano_obra, 2) }}</strong> + Repuestos: <strong>{{ number_format($orden->total_repuestos, 2) }}</strong>@if((float) $orden->descuento > 0) − Descuento: <strong>{{ number_format($orden->descuento, 2) }}</strong>@endif</p>
<p class="text-lg">Total: <strong>{{ number_format($orden->total, 2) }} Bs</strong></p>
<p class="text-xs text-gray-500">Son: {{ \App\Support\NumeroLetras::convertir((float) $orden->total) }}</p>
</div>
<h3 class="font-bold text-sm mt-3">Dinero recibido</h3>
<p class="text-sm">Ya dio: <strong>{{ number_format($orden->payments->sum('recibido'), 2) }}</strong> · Falta: <strong>{{ number_format(max(0, $orden->total - $orden->payments->sum('recibido')), 2) }} Bs</strong></p>
@if($orden->payments->isEmpty())<p class="text-sm text-gray-500">Sin adelantos ni cobros.</p>@else
<table class="w-full text-sm mt-1"><thead><tr class="text-left border-b"><th>Medio</th><th class="text-right">Recibido</th><th class="text-right">Vuelto</th></tr></thead><tbody>
@foreach($orden->payments as $p)<tr class="border-b"><td>{{ $p->medio }}</td><td class="text-right">{{ number_format($p->recibido, 2) }}</td><td class="text-right">{{ number_format($p->vuelto, 2) }}</td></tr>@endforeach
</tbody></table>@endif
<h3 class="font-bold text-sm mt-3">Recepción del vehículo</h3>
@if(! $orden->recepcion)<p class="text-sm text-gray-500">Sin recepción.@if($orden->estado === 'registrada') <a href="/servicios/{{ $orden->id }}/recepcion" class="underline">Registrar</a>@endif</p>@else
<p class="text-sm">Combustible: <strong>{{ \App\Models\Recepcion::COMBUSTIBLE[$orden->recepcion->combustible] ?? $orden->recepcion->combustible }}</strong></p>
<p class="text-sm">Inventario OK: {{ collect($orden->recepcion->inventario ?? [])->map(fn ($k) => \App\Models\Recepcion::INVENTARIO[$k] ?? $k)->join(', ') ?: '—' }}</p>
@if($orden->recepcion->inventario_otros)<p class="text-sm">Otros: {{ $orden->recepcion->inventario_otros }}</p>@endif
<p class="text-sm">Inspección OK: {{ collect($orden->recepcion->inspeccion ?? [])->map(fn ($k) => \App\Models\Recepcion::INSPECCION[$k] ?? $k)->join(', ') ?: '—' }}</p>
@if($orden->recepcion->observaciones)<p class="text-sm">Obs: {{ $orden->recepcion->observaciones }}</p>@endif
@php($fr = $orden->recepcion->fotos->pluck('ruta', 'posicion')->toArray())
@if(! empty($fr))<div class="flex gap-2 flex-wrap mt-2">@foreach($fr as $pos => $ruta)<figure class="text-center"><img src="/storage/{{ $ruta }}" alt="Foto {{ $pos }}" class="w-24 h-24 object-cover rounded border"><figcaption class="text-xs">{{ \App\Models\RecepcionFoto::POSICIONES[$pos] ?? $pos }}</figcaption></figure>@endforeach</div>@endif
@endif
<div class="flex gap-3 mt-4 text-sm">
<x-accion tono="fuerte" :href="'/servicios/'.$orden->id.'/imprimir'" target="_blank">Imprimir</x-accion>
@if($orden->estado === 'registrada')<x-accion :href="'/servicios/'.$orden->id.'/editar'">Editar</x-accion><x-accion :href="'/servicios/'.$orden->id.'/recepcion'">Recepción</x-accion><x-accion tono="verde" :href="'/servicios/'.$orden->id.'/cobrar'">Cobrar</x-accion>@endif
</div>
</div>
