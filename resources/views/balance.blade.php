@extends('layouts.admin')
@section('content')
<div class="flex justify-between items-center mb-3 flex-wrap gap-2"><h1 class="text-xl font-bold">{{ $titulo }}</h1><div class="flex gap-2"><a href="/contabilidad/balance/csv" class="bg-green-700 text-white px-3 py-2 rounded text-sm">⬇ CSV</a><x-accion href="/contabilidad">Volver</x-accion></div></div>
<div class="bg-white p-4 rounded shadow"><table class="w-full text-sm"><thead><tr class="text-left border-b"><th>Grupo</th><th class="text-right">Monto</th></tr></thead><tbody>@foreach($filas as $f)<tr class="border-b"><td>{{ $f['nombre'] }}</td><td class="text-right">{{ number_format($f['monto'], 2) }}</td></tr>@endforeach</tbody></table>
<p class="text-sm mt-2">{{ $nota }}</p></div>
@endsection
