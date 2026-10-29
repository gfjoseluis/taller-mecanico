@extends('layouts.admin')
@section('content')
<h1 class="text-2xl font-bold num mb-4">Dashboard</h1>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
<div class="bg-white p-4 rounded shadow"><p class="text-sm text-gray-500">Servicio abiertas</p><p class="text-2xl font-bold num">{{ $abiertas }}</p></div>
<div class="bg-white p-4 rounded shadow"><p class="text-sm text-gray-500">Ventas hoy (facturado)</p><p class="text-2xl font-bold num">{{ number_format($ventasHoy, 2) }}</p></div>
<div class="bg-white p-4 rounded shadow"><p class="text-sm text-gray-500">Stock bajo</p><p class="text-2xl font-bold num text-red-600">{{ $stockBajo }}</p></div>
<div class="bg-white p-4 rounded shadow"><p class="text-sm text-gray-500">Saldo bancos</p><p class="text-2xl font-bold num">{{ number_format($saldoBancos, 2) }}</p></div>
<div class="bg-white p-4 rounded shadow"><p class="text-sm text-gray-500">Por cobrar (clientes deben)</p><p class="text-2xl font-bold num text-red-700">{{ number_format($porCobrar, 2) }}</p><a href="/por-cobrar" class="text-xs underline">Ver lista</a></div>
<div class="bg-white p-4 rounded shadow"><p class="text-sm text-gray-500">Por pagar (debemos)</p><p class="text-2xl font-bold num text-red-700">{{ number_format($porPagar, 2) }}</p><a href="/por-pagar" class="text-xs underline">Ver lista</a></div>
</div>
<div class="grid md:grid-cols-2 gap-4 mt-4">
<div class="bg-white p-4 rounded shadow"><h2 class="font-bold mb-2">Cobrado últimos 14 días</h2><canvas id="chVentas" height="120"></canvas></div>
<div class="bg-white p-4 rounded shadow"><h2 class="font-bold mb-2">Órdenes por estado</h2><canvas id="chEstados" height="120"></canvas></div>
</div>
<script src="/vendor/chartjs/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chVentas'), {type: 'bar', data: {labels: @json($labels14), datasets: [{label: 'Bs cobrados', data: @json($datos14)}]}, options: {plugins: {legend: {display: false}}}});
new Chart(document.getElementById('chEstados'), {type: 'doughnut', data: {labels: @json($estadosLabels), datasets: [{data: @json($estadosDatos)}]}});
</script>
<div class="bg-white p-4 rounded shadow mt-4">
<h2 class="font-bold mb-2">Últimos servicios</h2>
<table class="w-full text-sm"><thead><tr class="text-left border-b"><th>Número</th><th>Cliente</th><th>Estado</th><th>Total</th></tr></thead>
<tbody>@foreach($ultimas as $ot)<tr class="border-b"><td>{{ $ot->numero }}</td><td>{{ $ot->client->nombre ?? '' }}</td><td><x-estado :texto="$ot->estado" /></td><td>{{ number_format($ot->total, 2) }}</td></tr>@endforeach</tbody></table>
</div>
@endsection
