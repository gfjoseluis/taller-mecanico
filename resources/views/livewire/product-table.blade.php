<div>
<div class="bg-white p-4 rounded shadow">
<div class="flex justify-between mb-2"><h2 class="font-bold">Inventario — Productos</h2><a href="/productos/crear" class="bg-slate-900 text-white px-4 py-2 rounded text-sm">+ Nuevo</a></div>
<p class="text-xs text-gray-500 mb-2">Glosario: <strong>Costo</strong> = lo pagado al proveedor (auto al comprar) · <strong>Precio de venta</strong> = lo cobrado al cliente · <strong>Margen</strong> = venta − costo.</p>
<div class="flex gap-2 mb-2"><input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o SKU... Ej: filtro, REP-FILT" class="border p-2 rounded w-full"><label class="text-sm flex items-center gap-1"><input type="checkbox" wire:model.live="verEliminados"> Eliminados</label></div>
<table class="w-full text-sm"><thead><tr class="text-left border-b"><th>SKU</th><th>Nombre</th><th>Tipo</th><th>Costo</th><th>Venta</th><th>Margen</th><th>Stock</th><th></th></tr></thead>
<tbody>@foreach($items as $p)<tr class="border-b"><td class="font-mono">{{ $p->sku }}</td><td>{{ $p->nombre }}</td><td>{{ $p->tipo }}</td><td>{{ number_format($p->precio_compra, 2) }}</td><td>{{ number_format($p->precio_venta, 2) }}</td><td class="text-green-700">{{ number_format($p->precio_venta - $p->precio_compra, 2) }}</td><td>{{ $p->stock }}</td>
<td class="flex gap-2"><a href="/productos/{{ $p->id }}/editar" class="underline">Editar</a>@if($p->trashed())<button wire:click="restaurar({{ $p->id }})" class="text-green-700 underline">Restaurar</button>@else<button wire:click="eliminar({{ $p->id }})" class="text-red-600 underline">Eliminar</button>@endif</td></tr>@endforeach</tbody></table>
<div class="mt-2">{{ $items->links() }}</div>
</div>
</div>
