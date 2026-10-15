@extends('layouts.admin')
@section('content')
<h1 class="text-xl font-bold mb-3">Por cobrar (clientes que deben)</h1>
@if(session('ok'))<p class="bg-green-100 p-2 rounded mb-2">{{ session('ok') }}</p>@endif
<div class="bg-white p-4 rounded shadow"><table class="w-full text-sm"><thead><tr class="text-left border-b"><th>Orden</th><th>Cliente</th><th>Total</th><th>Cobrado</th><th>Deuda</th><th>Días</th><th></th></tr></thead><tbody>@foreach($items as $ot)<tr class="border-b"><td class="font-mono">{{ $ot->numero }}</td><td>{{ $ot->client->nombre ?? '' }}</td><td>{{ number_format($ot->total, 2) }}</td><td>{{ number_format($ot->payments_sum_recibido ?? 0, 2) }}</td><td class="font-bold text-red-700">{{ number_format($ot->total - ($ot->payments_sum_recibido ?? 0), 2) }}</td><td>{{ $ot->cerrada_at ? $ot->cerrada_at->diffInDays(now()) : '—' }}</td><td><a href="/por-cobrar/{{ $ot->id }}/abonar" class="underline font-bold">Abonar</a></td></tr>@endforeach</tbody></table><div class="mt-2">{{ $items->links() }}</div></div>
@endsection
