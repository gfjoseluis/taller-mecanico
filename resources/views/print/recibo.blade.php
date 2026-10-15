<h1 style="font-family:sans-serif">Recibo — Orden {{ $ot->numero }}</h1>
<p>Cliente: {{ $ot->client->nombre }} ({{ $ot->client->nit_ci }})</p>
<p>Vehículo: {{ $ot->vehicle->placa }} {{ $ot->vehicle->marca }} {{ $ot->vehicle->modelo }}</p>
<p>Total: {{ number_format($ot->total, 2) }} Bs</p>
@if($pago)
<p>Cobro: {{ $pago->medio }} | Recibido: {{ number_format($pago->recibido, 2) }} | Vuelto: {{ number_format($pago->vuelto, 2) }} @if($pago->saldo_pendiente > 0)| Deuda: {{ number_format($pago->saldo_pendiente, 2) }}@endif</p>
@endif
<p>Estado: {{ $ot->estado }}</p>
