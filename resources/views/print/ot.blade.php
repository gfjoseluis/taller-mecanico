<h1 style="font-family:sans-serif">OT {{ $ot->numero }}</h1>
<p>Cliente: {{ $ot->client->nombre }} ({{ $ot->client->nit_ci }})</p>
<p>Vehículo: {{ $ot->vehicle->placa }} {{ $ot->vehicle->marca }} {{ $ot->vehicle->modelo }}</p>
<p>Estado: {{ $ot->estado }} | Total: {{ number_format($ot->total, 2) }}</p>
<p>Diagnóstico: {{ $ot->diagnostico }}</p>
