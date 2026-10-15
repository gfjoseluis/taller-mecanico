<div>
<div class="bg-white p-4 rounded shadow"><div class="flex justify-between mb-2"><h2 class="font-bold">Vehículos</h2><a href="/vehiculos/crear" class="bg-slate-900 text-white px-4 py-2 rounded text-sm">+ Nuevo</a></div><input wire:model.live.debounce.300ms="search" placeholder="Buscar placa/chasis... Ej: 1234-ABC" class="border p-2 rounded w-full mb-2">
<table class="w-full text-sm"><thead><tr class="text-left border-b"><th>Placa</th><th>Cliente</th><th>Marca</th><th>Modelo</th><th></th></tr></thead><tbody>@foreach($items as $v)<tr class="border-b"><td class="font-mono">{{ $v->placa }}</td><td>{{ $v->client->nombre ?? '' }}</td><td>{{ $v->marca }}</td><td>{{ $v->modelo }}</td><td class="flex gap-2"><a href="/vehiculos/{{ $v->id }}/editar" class="underline">Editar</a><button wire:click="eliminar({{ $v->id }})" class="text-red-600 underline">Eliminar</button></td></tr>@endforeach</tbody></table>
<div class="mt-2">{{ $items->links() }}</div></div>
</div>
