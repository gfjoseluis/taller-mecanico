<h1>{{ $empresa }}</h1>
<p class="muted">ORDEN DE SERVICIO {{ $ot->numero }} · Estado: {{ $ot->estado }} · Fecha: {{ $ot->created_at?->format('d/m/Y') }}</p>
<table class="grid"><tr>
<td><strong>Cliente:</strong> {{ $ot->client->nombre ?? '—' }}<br><span class="muted">NIT/CI: {{ $ot->client->nit_ci ?? '—' }} · Tel: {{ $ot->client->telefono ?? '—' }}</span></td>
<td><strong>Vehículo:</strong> {{ $ot->vehicle->placa ?? '—' }}<br><span class="muted">{{ $ot->vehicle->marca ?? '' }} {{ $ot->vehicle->modelo ?? '' }} · Chasis: {{ $ot->vehicle->chasis ?? '—' }}@if($ot->recepcion?->kilometraje) · Km: {{ number_format($ot->recepcion->kilometraje, 0) }}@endif</span></td>
</tr></table>
<h2>Diagnóstico</h2>
<p>{{ $diagnosticoBase }}</p>
@if(empty($grupos))<p class="muted">Sin servicios ni repuestos detallados.</p>@else
@foreach($grupos as $g)
<h2>{{ $g['titulo'] }}</h2>
@if($g['clave'] === 'servicio')
<table><thead><tr><th>Servicio a realizar</th><th class="num">Precio (Bs)</th></tr></thead><tbody>
@foreach($g['lineas'] as $l)<tr><td>{{ $l->etiqueta }}</td><td class="num">{{ number_format($l->precio, 2) }}</td></tr>@endforeach
</tbody></table>
<p class="muted">Subtotal servicios: {{ number_format($g['subtotal'], 2) }}</p>
@else
<table><thead><tr><th>{{ $g['clave'] === 'repuesto' ? 'Repuesto' : 'Insumo' }}</th><th class="num">Cantidad</th><th class="num">Precio aplicado (Bs)</th><th class="num">Subtotal</th></tr></thead><tbody>
@foreach($g['lineas'] as $l)<tr><td>{{ $l->etiqueta }} <span class="muted">{{ $l->referencia }}</span></td><td class="num">{{ number_format($l->cantidad, 2) }}</td><td class="num">{{ number_format($l->precio, 2) }}</td><td class="num">{{ number_format($l->cantidad * $l->precio, 2) }}</td></tr>@endforeach
</tbody></table>
<p class="muted">Subtotal {{ $g['clave'] === 'repuesto' ? 'repuestos' : 'insumos' }}: {{ number_format($g['subtotal'], 2) }}</p>
@endif
@endforeach
@endif
<p class="total">Mano de obra: {{ number_format($ot->mano_obra, 2) }} + Repuestos: {{ number_format($ot->total_repuestos, 2) }}@if((float) $ot->descuento > 0) − Descuento: {{ number_format($ot->descuento, 2) }}@endif = <strong>Total: {{ number_format($ot->total, 2) }} Bs</strong></p>
<p class="muted">Son: {{ \App\Support\NumeroLetras::convertir((float) $ot->total) }}</p>
<h2>Recepción del vehículo</h2>
@if(! $ot->recepcion)<p class="muted">Sin recepción registrada.</p>@else
<p>Combustible: <strong>{{ \App\Models\Recepcion::COMBUSTIBLE[$ot->recepcion->combustible] ?? $ot->recepcion->combustible }}</strong></p>
<p>Inventario OK: {{ collect($ot->recepcion->inventario ?? [])->map(fn ($k) => \App\Models\Recepcion::INVENTARIO[$k] ?? $k)->join(', ') ?: '—' }}</p>
@if($ot->recepcion->inventario_otros)<p>Otros: {{ $ot->recepcion->inventario_otros }}</p>@endif
<p>Inspección OK: {{ collect($ot->recepcion->inspeccion ?? [])->map(fn ($k) => \App\Models\Recepcion::INSPECCION[$k] ?? $k)->join(', ') ?: '—' }}</p>
@if($ot->recepcion->observaciones)<p>Obs: {{ $ot->recepcion->observaciones }}</p>@endif
@php($fr = $ot->recepcion->fotos->pluck('ruta', 'posicion')->toArray())
@if(! empty($fr))<table><tr>@foreach($fr as $pos => $ruta)<td style="text-align:center"><img src="/storage/{{ $ruta }}" style="width:110px" alt="{{ $pos }}"><br><span class="muted">{{ \App\Models\RecepcionFoto::POSICIONES[$pos] ?? $pos }}</span></td>@endforeach</tr></table>@endif
@endif
<h2>Dinero</h2>
@php($yaDio = $ot->payments->sum('recibido'))
@if($ot->payments->isEmpty())<p class="muted">Sin dinero recibido.</p>@else
<table><thead><tr><th>Fecha</th><th>Medio</th><th class="num">Monto</th></tr></thead><tbody>
@foreach($ot->payments as $p)<tr><td>{{ $p->created_at?->format('d/m/Y') }}</td><td>{{ $p->medio }}</td><td class="num">{{ number_format($p->recibido, 2) }}</td></tr>@endforeach
</tbody></table>@endif
<p class="total">Ya dio: <strong>{{ number_format($yaDio, 2) }}</strong> · Falta: <strong>{{ number_format(max(0, $ot->total - $yaDio), 2) }} Bs</strong>@if($ot->payments->last()?->vuelto > 0) · Vuelto: {{ number_format($ot->payments->last()->vuelto, 2) }}@endif</p>
<table class="firmas"><tr>
<td><p class="linea">Firma cliente (recibí conforme)</p></td>
<td><p class="linea">Firma encargado de caja</p></td>
</tr></table>
