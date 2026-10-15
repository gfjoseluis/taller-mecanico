<div class="space-y-4">
<div class="bg-white p-4 rounded shadow">
<div class="flex justify-between"><h2 class="font-bold text-lg">{{ $cliente->nombre }}</h2><a href="/clientes/{{ $cliente->id }}/editar" class="underline text-sm">Editar cliente</a></div>
<p class="text-sm text-gray-600">NIT/CI: {{ $cliente->nit_ci }} | Tel: {{ $cliente->telefono }} | {{ $cliente->direccion }}</p>
</div>
<div class="bg-white p-4 rounded shadow">
<div class="flex justify-between mb-2"><h3 class="font-bold">Vehículos del cliente ({{ $cliente->vehicles->count() }})</h3><a href="/vehiculos/crear?cliente={{ $cliente->id }}" class="bg-slate-900 text-white px-3 py-1 rounded text-sm">+ Vehículo</a></div>
<table class="w-full text-sm"><thead><tr class="text-left border-b"><th>Placa</th><th>Marca</th><th>Modelo</th><th>Chasis</th><th></th></tr></thead>
<tbody>@foreach($cliente->vehicles as $v)<tr class="border-b"><td class="font-mono">{{ $v->placa }}</td><td>{{ $v->marca }}</td><td>{{ $v->modelo }}</td><td>{{ $v->chasis }}</td><td class="flex gap-2 flex-wrap"><a href="/servicios/crear?cliente={{ $cliente->id }}&vehiculo={{ $v->id }}" class="underline text-blue-700 font-bold">Atender este vehículo</a><a href="/vehiculos/{{ $v->id }}/editar" class="underline">Editar</a><button wire:click="eliminarVehiculo({{ $v->id }})" class="text-red-600 underline">Eliminar</button></td></tr>@endforeach</tbody></table>
</div>
</div>
