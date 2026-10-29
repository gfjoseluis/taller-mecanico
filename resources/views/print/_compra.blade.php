<h1>{{ $empresa }}</h1>
<p class="muted">Compra {{ $c->folio }} · {{ $c->tipo_pago }} · Estado: {{ $c->estado }} · Fecha: {{ $c->created_at?->format('d/m/Y') }}</p>
<table class="grid"><tr>
<td><strong>Proveedor:</strong> {{ $c->supplier->nombre ?? '—' }}<br><span class="muted">NIT: {{ $c->supplier->nit ?? '—' }} · Tel: {{ $c->supplier->telefono ?? '—' }}</span></td>
<td><strong>Nota:</strong> {{ $c->glosa ?: '—' }}</td>
</tr></table>
<h2>Líneas de compra</h2>
@if($c->lines->isEmpty())<p class="muted">Sin líneas.</p>@else
<table><thead><tr><th>Producto</th><th class="num">Cantidad</th><th class="num">Costo (Bs)</th><th class="num">Subtotal</th></tr></thead><tbody>
@foreach($c->lines as $l)<tr><td>{{ $l->product->nombre ?? '—' }} <span class="muted">{{ $l->product->sku ?? '' }}</span></td><td class="num">{{ number_format($l->cantidad, 2) }}</td><td class="num">{{ number_format($l->costo, 2) }}</td><td class="num">{{ number_format($l->cantidad * $l->costo, 2) }}</td></tr>@endforeach
</tbody></table>@endif
<p class="total">Total: <strong>{{ number_format($c->total, 2) }} Bs</strong> · Deuda: <strong>{{ number_format($c->saldo, 2) }} Bs</strong></p>
<h2>Abonos</h2>
@if($c->abonos->isEmpty())<p class="muted">Sin abonos.</p>@else
<table><thead><tr><th>Medio</th><th>Cuenta</th><th class="num">Monto</th></tr></thead><tbody>
@foreach($c->abonos as $a)<tr><td>{{ $a->medio }}</td><td>{{ $a->banco->nombre ?? 'caja' }}</td><td class="num">{{ number_format($a->monto, 2) }}</td></tr>@endforeach
</tbody></table>@endif
<table class="firmas"><tr>
<td><p class="linea">Firma proveedor (recibí conforme)</p></td>
<td><p class="linea">Firma encargado de caja</p></td>
</tr></table>
