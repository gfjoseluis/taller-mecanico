<h1>{{ $empresa }}</h1>
<p class="muted">PRO-FORMA {{ $p->numero }} · Fecha: {{ $p->created_at?->format('d/m/Y') }} · Válida antes de convertirse en orden</p>
<table class="grid"><tr>
<td><strong>Cliente:</strong> {{ $p->client->nombre ?? '—' }}<br><span class="muted">NIT/CI: {{ $p->client->nit_ci ?? '—' }} · Tel: {{ $p->client->telefono ?? '—' }}</span></td>
<td><strong>Vehículo:</strong> {{ $p->vehicle->placa ?? '—' }}<br><span class="muted">{{ $p->vehicle->marca ?? '' }} {{ $p->vehicle->modelo ?? '' }}</span></td>
</tr></table>
<h2>Detalle</h2>
<p>{{ $diagnosticoBase }}</p>
@if(empty($grupos))<p class="muted">Sin servicios ni repuestos detallados.</p>@else
@foreach($grupos as $g)
<h2>{{ $g['titulo'] }}</h2>
@if($g['clave'] === 'servicio')
<table><thead><tr><th>Servicio</th><th class="num">Precio (Bs)</th></tr></thead><tbody>
@foreach($g['lineas'] as $l)<tr><td>{{ $l->etiqueta }}</td><td class="num">{{ number_format($l->precio, 2) }}</td></tr>@endforeach
</tbody></table>
<p class="muted">Subtotal servicios: {{ number_format($g['subtotal'], 2) }}</p>
@else
<table><thead><tr><th>{{ $g['clave'] === 'repuesto' ? 'Repuesto' : 'Insumo' }}</th><th class="num">Cantidad</th><th class="num">Precio (Bs)</th><th class="num">Subtotal</th></tr></thead><tbody>
@foreach($g['lineas'] as $l)<tr><td>{{ $l->etiqueta }}</td><td class="num">{{ number_format($l->cantidad, 2) }}</td><td class="num">{{ number_format($l->precio, 2) }}</td><td class="num">{{ number_format($l->cantidad * $l->precio, 2) }}</td></tr>@endforeach
</tbody></table>
<p class="muted">Subtotal {{ $g['clave'] === 'repuesto' ? 'repuestos' : 'insumos' }}: {{ number_format($g['subtotal'], 2) }}</p>
@endif
@endforeach
@endif
<p class="total">Mano de obra: {{ number_format($p->mano_obra, 2) }} + Repuestos: {{ number_format($p->total_repuestos, 2) }}@if((float) $p->descuento > 0) − Descuento: {{ number_format($p->descuento, 2) }}@endif = <strong>Total: {{ number_format($p->total, 2) }} Bs</strong></p>
<p class="muted">Son: {{ \App\Support\NumeroLetras::convertir((float) $p->total) }}</p>
<table class="firmas"><tr>
<td><p class="linea">Firma cliente (acepto el presupuesto)</p></td>
<td><p class="linea">Firma encargado</p></td>
</tr></table>
