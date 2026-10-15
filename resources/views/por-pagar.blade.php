@extends('layouts.admin')
@section('content')
<h1 class="text-xl font-bold mb-3">Por pagar (debemos a importadoras)</h1>
@if(session('ok'))<p class="bg-green-100 p-2 rounded mb-2">{{ session('ok') }}</p>@endif
<div class="bg-white p-4 rounded shadow"><table class="w-full text-sm"><thead><tr class="text-left border-b"><th>Folio</th><th>Importadora</th><th>Total</th><th>Deuda</th><th>Días</th><th></th></tr></thead><tbody>@foreach($items as $c)<tr class="border-b"><td class="font-mono">{{ $c->folio }}</td><td>{{ $c->supplier->nombre ?? '' }}</td><td>{{ number_format($c->total, 2) }}</td><td class="font-bold text-red-700">{{ number_format($c->saldo, 2) }}</td><td>{{ $c->created_at->diffInDays(now()) }}</td><td><a href="/por-pagar/{{ $c->id }}/abonar" class="underline font-bold">Abonar</a></td></tr>@endforeach</tbody></table><div class="mt-2">{{ $items->links() }}</div></div>
@endsection
