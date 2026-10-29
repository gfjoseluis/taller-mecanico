<div class="bg-white p-4 rounded shadow w-full">
<h2 class="font-bold mb-3">{{ $editId ? 'Editar' : 'Nuevo' }} producto (SKU autogenerado)</h2>
<x-field label="Nombre" hint="Ej: Filtro de aceite Volvo"><input wire:model="nombre" placeholder="Ej: Filtro de aceite Volvo" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Categoría*" hint="Obligatoria: define tipo y SKU. Ej: Filtros, Llantas..."><livewire:searchable-select modelo="Category" :value="$category_id" placeholder="Ej: Filtros, Aceites..." /></x-field>
@error('category_id')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<x-field label="Último costo (Bs)" hint="Lo pagado al proveedor. Se actualiza solo al comprar, no se edita. Ej: 80.00">@if($editId)<input value="{{ number_format($precio_compra, 2) }}" readonly class="border p-2 rounded w-full mt-1 bg-gray-100">@else<details class="mt-1"><summary class="text-xs text-gray-500 underline cursor-pointer">¿Conoces el costo? (opcional — si no, queda en 0 y se llena con tu primera compra)</summary><input wire:model="precio_compra" type="number" step="0.01" placeholder="Ej: 80.00 (costo inicial, opcional)" class="border p-2 rounded w-full mt-1"></details>@endif</x-field>
<x-field label="Precio de venta (Bs)" hint="Lo cobrado al cliente. Ej: 120.00. Margen = venta − costo"><input wire:model="precio_venta" type="number" step="0.01" placeholder="Ej: 120.00" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Stock actual" hint="Solo entra por compras, aquí no se escribe. Ej: compra primero">@if($editId)<input value="{{ number_format($stock, 2) }}" readonly aria-label="Stock actual (solo lectura)" class="border p-2 rounded w-full mt-1 bg-gray-100">@else<input value="0 (entra por compras)" readonly aria-label="Stock inicial siempre en cero" class="border p-2 rounded w-full mt-1 bg-gray-100">@endif</x-field>
<x-field label="Stock mínimo" hint="Ej: 2 (alerta)"><input wire:model="stock_minimo" type="number" step="0.01" placeholder="Ej: 2" class="border p-2 rounded w-full mt-1"></x-field>
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar</button>
<x-accion href="/productos">Volver</x-accion>
</div>
