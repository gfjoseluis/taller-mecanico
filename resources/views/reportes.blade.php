@extends('layouts.admin')
@section('content')
<div class="flex justify-between items-center mb-3 flex-wrap gap-2"><h1 class="text-xl font-bold">Reportes</h1>
<div class="flex gap-2 flex-wrap text-sm"><a href="/reportes/ventas" class="border px-3 py-1 rounded {{ $seccion === 'ventas' ? 'bg-slate-900 text-white' : '' }}">Ventas</a><a href="/reportes/top" class="border px-3 py-1 rounded {{ $seccion === 'top' ? 'bg-slate-900 text-white' : '' }}">Top repuestos</a><a href="/reportes/mecanicos" class="border px-3 py-1 rounded {{ $seccion === 'mecanicos' ? 'bg-slate-900 text-white' : '' }}">Por mecánico</a><a href="/reportes/ganancia" class="border px-3 py-1 rounded {{ $seccion === 'ganancia' ? 'bg-slate-900 text-white' : '' }}">Ganancia</a><a href="/reportes/bancos" class="border px-3 py-1 rounded {{ $seccion === 'bancos' ? 'bg-slate-900 text-white' : '' }}">Bancos</a><a href="/reportes/{{ $seccion }}?csv=1&desde={{ $desde }}&hasta={{ $hasta }}" class="bg-green-700 text-white px-3 py-1 rounded">⬇ CSV</a></div></div>
<form method="GET" action="/reportes/{{ $seccion }}" class="bg-white p-3 rounded shadow mb-3 flex gap-2 text-sm"><label>Desde <input type="date" name="desde" value="{{ $desde }}" class="border p-1 rounded"></label><label>Hasta <input type="date" name="hasta" value="{{ $hasta }}" class="border p-1 rounded"></label><button class="bg-slate-900 text-white px-3 py-1 rounded">Filtrar</button></form>
<div class="bg-white p-4 rounded shadow mb-4"><div style="height:320px;position:relative"><canvas id="chRep"></canvas></div></div>
<div class="bg-white p-4 rounded shadow"><table class="w-full text-sm"><tbody>
@if($seccion === 'ventas')@foreach($datos as $d)<tr class="border-b"><td>{{ $d->m }}</td><td class="text-right">{{ number_format($d->t, 2) }}</td></tr>@endforeach
@elseif($seccion === 'top')@foreach($datos as $d)<tr class="border-b"><td>{{ $d->product->sku ?? '' }} {{ $d->product->nombre ?? '' }}</td><td class="text-right">{{ $d->cant }} uds.</td><td class="text-right">{{ number_format($d->total, 2) }}</td></tr>@endforeach
@elseif($seccion === 'mecanicos')@foreach($datos as $d)<tr class="border-b"><td>{{ $d->tecnico->name ?? 'Sin asignar' }}</td><td class="text-right">{{ $d->n }} órdenes</td><td class="text-right">{{ number_format($d->t, 2) }}</td></tr>@endforeach
@elseif($seccion === 'ganancia')@foreach($datos['ingresos'] as $m => $t)<tr class="border-b"><td>{{ $m }}</td><td class="text-right text-green-700">+{{ number_format($t, 2) }}</td><td class="text-right text-red-700">−{{ number_format($datos['gastos'][$m] ?? 0, 2) }}</td><td class="text-right font-bold">{{ number_format($t - ($datos['gastos'][$m] ?? 0), 2) }}</td></tr>@endforeach
@elseif($seccion === 'bancos')@foreach($datos as $d)<tr class="border-b"><td>{{ $d->cuenta->nombre ?? '' }}</td><td>{{ $d->tipo }}</td><td class="text-right">{{ number_format($d->t, 2) }}</td></tr>@endforeach
@endif
</tbody></table></div>
<script src="/vendor/chartjs/chart.umd.min.js"></script>
<script>
const S = @json($seccion);
let labels = [], vals = [], vals2 = null;
@if($seccion === 'ventas') labels = @json($datos->pluck('m')); vals = @json($datos->pluck('t'));
@elseif($seccion === 'top') labels = @json($datos->map(fn($d) => $d->product->sku ?? '')); vals = @json($datos->pluck('cant'));
@elseif($seccion === 'mecanicos') labels = @json($datos->map(fn($d) => $d->tecnico->name ?? 'Sin asignar')); vals = @json($datos->pluck('n'));
@elseif($seccion === 'ganancia') labels = @json(array_keys($datos['ingresos']->toArray())); vals = @json(array_values($datos['ingresos']->toArray())); vals2 = @json(array_values($datos['gastos']->toArray()));
@elseif($seccion === 'bancos') labels = @json($datos->map(fn($d) => ($d->cuenta->nombre ?? '').'/'.$d->tipo)); vals = @json($datos->pluck('t'));
@endif
const ds = [{label: S, data: vals}];
if (vals2) ds.push({label: 'gastos', data: vals2});
new Chart(document.getElementById('chRep'), {type: S === 'mecanicos' || S === 'bancos' ? 'doughnut' : 'bar', data: {labels, datasets: ds}, options: {responsive: true, maintainAspectRatio: false}});
</script>
@endsection
