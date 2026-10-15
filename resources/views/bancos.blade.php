@extends('layouts.admin')
@section('content')
<div class="flex justify-between items-center mb-3"><h1 class="text-xl font-bold">Bancos (4 cuentas)</h1><div class="flex gap-2"><a href="/movimientos" class="border px-4 py-2 rounded text-sm">Movimientos</a><a href="/bancos/crear" class="bg-slate-900 text-white px-4 py-2 rounded text-sm">+ Nueva cuenta</a></div></div>
@if(session('ok'))<p class="bg-green-100 p-2 rounded mb-2">{{ session('ok') }}</p>@endif
<p class="text-xs text-gray-500 mb-2">El saldo nunca se edita a mano: es el inicial al corte + movimientos. ✓ = calculado cuadra.</p>
<div class="grid md:grid-cols-4 gap-4">@foreach($cuentas as $b)<div class="bg-white p-4 rounded shadow"><p class="font-bold">{{ $b->nombre }}</p><p class="text-sm">{{ $b->banco }} - {{ $b->numero }}</p><p class="text-xl">{{ number_format($b->saldo, 2) }} {{ $b->moneda }}</p><p class="text-xs {{ abs($b->saldo - $b->calculado) < 0.01 ? 'text-green-700' : 'text-red-700' }}">{{ abs($b->saldo - $b->calculado) < 0.01 ? '✓ cuadra' : '⚠ difiere: '.number_format($b->calculado, 2) }}</p><a href="/bancos/{{ $b->id }}/editar" class="text-sm underline">Editar</a></div>@endforeach</div>
@endsection
