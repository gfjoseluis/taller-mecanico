<div class="bg-white p-4 rounded shadow w-full">
<div class="flex justify-between items-start mb-2">
<h2 class="font-bold">Pro-forma {{ $proforma->numero }} <x-estado :texto="$proforma->estado" /></h2>
<x-accion href="/proformas">Volver</x-accion>
</div>
<div class="grid grid-cols-2 gap-2 text-sm mb-3">
<div><p class="text-gray-500 text-xs">Cliente</p><p class="font-bold">{{ $proforma->client->nombre ?? '—' }}</p><p class="text-xs">NIT/CI: {{ $proforma->client->nit_ci ?? '—' }} · {{ $proforma->client->telefono ?? '' }}</p></div>
<div><p class="text-gray-500 text-xs">Vehículo</p><p class="font-bold font-mono">{{ $proforma->vehicle->placa ?? '—' }}</p><p class="text-xs">{{ $proforma->vehicle->marca ?? '' }} {{ $proforma->vehicle->modelo ?? '' }}</p></div>
</div>
<h3 class="font-bold text-sm mt-2">Detalle</h3>
<p class="text-sm whitespace-pre-line">{{ $diagnosticoBase }}</p>
@if(empty($grupos))<p class="text-sm text-gray-500 mt-3">Sin servicios ni repuestos.</p>@else
@foreach($grupos as $g)
<h3 class="font-bold text-sm mt-3">{{ $g['titulo'] }}</h3>
@if($g['clave'] === 'servicio')
<table class="w-full text-sm mt-1"><thead><tr class="text-left border-b"><th>Servicio</th><th class="text-right">Precio (Bs)</th></tr></thead><tbody>
@foreach($g['lineas'] as $l)<tr class="border-b"><td>{{ $l->etiqueta }}</td><td class="text-right">{{ number_format($l->precio, 2) }}</td></tr>@endforeach
</tbody></table>
@else
<table class="w-full text-sm mt-1"><thead><tr class="text-left border-b"><th>{{ $g['clave'] === 'repuesto' ? 'Repuesto' : 'Insumo' }}</th><th class="text-right">Cantidad</th><th class="text-right">Precio (Bs)</th><th class="text-right">Subtotal</th></tr></thead><tbody>
@foreach($g['lineas'] as $l)<tr class="border-b"><td>{{ $l->etiqueta }}</td><td class="text-right">{{ number_format($l->cantidad, 2) }}</td><td class="text-right">{{ number_format($l->precio, 2) }}</td><td class="text-right">{{ number_format($l->cantidad * $l->precio, 2) }}</td></tr>@endforeach
</tbody></table>
@endif
@endforeach
@endif
<div class="bg-gray-50 rounded p-3 mt-3 text-sm">
<p>Subtotal: <strong>{{ number_format($proforma->mano_obra + $proforma->total_repuestos, 2) }}</strong>@if((float) $proforma->descuento > 0) − Descuento: <strong>{{ number_format($proforma->descuento, 2) }}</strong>@endif</p>
<p class="text-lg">Total: <strong>{{ number_format($proforma->total, 2) }} Bs</strong></p>
<p class="text-xs text-gray-500">Son: {{ \App\Support\NumeroLetras::convertir((float) $proforma->total) }}</p>
</div>
<div class="flex gap-3 mt-4 text-sm">
<x-accion tono="fuerte" :href="'/proformas/'.$proforma->id.'/imprimir'" target="_blank">Imprimir</x-accion>
@if($proforma->estado === 'pendiente')<x-accion :href="'/proformas/'.$proforma->id.'/editar'">Editar</x-accion><form method="POST" action="/proformas/{{ $proforma->id }}/convertir" class="inline" onsubmit="return confirmarForm(event, 'Convertir a OT', 'Se crea la orden y descuenta stock. ¿Seguro?')">@csrf<x-accion tono="verde" type="submit">Convertir a OT</x-accion></form>@endif
@if($proforma->orden)<p class="text-sm text-gray-600">OT generada: <a href="/servicios/{{ $proforma->orden->id }}" class="underline font-mono">{{ $proforma->orden->numero }}</a></p>@endif
</div>
</div>
