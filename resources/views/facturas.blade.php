@extends('layouts.admin')
@section('content')
<h1 class="text-xl font-bold mb-3">Facturas SIAT (mock/offline-ready)</h1>
@if(session('ok'))<p class="bg-green-100 p-2 rounded mb-2">{{ session('ok') }}</p>@endif
<form method="POST" action="/facturas/emitir-demo">@csrf<button class="bg-slate-900 text-white px-4 py-2 rounded mb-3">Emitir demo (cola mock)</button></form>
<div class="bg-white p-4 rounded shadow"><table class="w-full text-sm"><thead><tr class="text-left border-b"><th>CUF</th><th>Total</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>@foreach($items as $f)<tr class="border-b"><td class="font-mono text-xs">{{ $f->cuf }}</td><td>{{ number_format($f->total, 2) }}</td><td>{{ $f->estado }}</td><td>{{ $f->created_at }}</td></tr>@endforeach</tbody></table><div class="mt-2">{{ $items->links() }}</div></div>
@endsection
