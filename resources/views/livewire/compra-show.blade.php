<div class="bg-white p-4 rounded shadow w-full">
<div class="flex justify-between items-start mb-2">
<h2 class="font-bold">Compra {{ $compra->folio }} <span class="font-normal text-gray-500">({{ $compra->tipo_pago }})</span> <x-estado :texto="$compra->estado" /></h2>
<x-accion href="/compras">Volver</x-accion>
</div>
<div class="grid grid-cols-2 gap-2 text-sm mb-3">
<div><p class="text-gray-500 text-xs">Proveedor</p><p class="font-bold">{{ $compra->supplier->nombre ?? '—' }}</p><p class="text-xs">NIT: {{ $compra->supplier->nit ?? '—' }} · {{ $compra->supplier->telefono ?? '' }}</p></div>
<div><p class="text-gray-500 text-xs">Nota</p><p class="text-sm">{{ $compra->glosa ?: '—' }}</p></div>
</div>
<h3 class="font-bold text-sm mt-2">Líneas de compra</h3>
@if($compra->lines->isEmpty())<p class="text-sm text-gray-500">Sin líneas.</p>@else
<table class="w-full text-sm mt-1"><thead><tr class="text-left border-b"><th>Producto</th><th class="text-right">Cantidad</th><th class="text-right">Costo (Bs)</th><th class="text-right">Subtotal</th></tr></thead><tbody>
@foreach($compra->lines as $l)<tr class="border-b"><td>{{ $l->product->nombre ?? '—' }} <span class="text-xs text-gray-500">{{ $l->product->sku ?? '' }}</span></td><td class="text-right">{{ number_format($l->cantidad, 2) }}</td><td class="text-right">{{ number_format($l->costo, 2) }}</td><td class="text-right">{{ number_format($l->cantidad * $l->costo, 2) }}</td></tr>@endforeach
</tbody></table>@endif
<div class="bg-gray-50 rounded p-3 mt-3 text-sm">
<p class="text-lg">Total: <strong>{{ number_format($compra->total, 2) }} Bs</strong> · Deuda: <strong class="text-red-700">{{ number_format($compra->saldo, 2) }}</strong></p>
</div>
<h3 class="font-bold text-sm mt-3">Abonos a la importadora</h3>
@if($compra->abonos->isEmpty())<p class="text-sm text-gray-500">Sin abonos.</p>@else
<table class="w-full text-sm mt-1"><thead><tr class="text-left border-b"><th>Medio</th><th>Cuenta</th><th class="text-right">Monto</th></tr></thead><tbody>
@foreach($compra->abonos as $a)<tr class="border-b"><td>{{ $a->medio }}</td><td>{{ $a->banco->nombre ?? 'caja' }}</td><td class="text-right">{{ number_format($a->monto, 2) }}</td></tr>@endforeach
</tbody></table>@endif
<div class="flex gap-3 mt-4 text-sm">
<x-accion tono="fuerte" :href="'/compras/'.$compra->id.'/imprimir'" target="_blank">Imprimir</x-accion>
@if($compra->estado === 'vigente' && (float) $compra->saldo > 0)<x-accion tono="verde" :href="'/por-pagar/'.$compra->id.'/abonar'">Abonar</x-accion>@endif
</div>
</div>
