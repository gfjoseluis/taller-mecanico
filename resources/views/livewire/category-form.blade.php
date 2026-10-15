<div class="bg-white p-4 rounded shadow max-w-xl">
<h2 class="font-bold mb-3">{{ $editId ? 'Editar' : 'Nueva' }} categoría</h2>
<x-field label="Nombre" hint="Ej: Filtros, Aceites, Frenos"><input wire:model="nombre" placeholder="Ej: Filtros" class="border p-2 rounded w-full mt-1"></x-field>
@error('nombre')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<x-field label="Prefijo SKU" hint="Ej: FILT (4 letras para SKU REP-FILT-000123)"><input wire:model="prefijo" placeholder="Ej: FILT" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Tipo" hint="Define prefijo global del SKU"><select wire:model="tipo" class="border p-2 rounded w-full mt-1"><option value="repuesto">Repuesto</option><option value="insumo">Insumo</option><option value="producto">Producto</option><option value="servicio">Servicio</option><option value="material">Material</option></select></x-field>
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar</button>
<a href="/categorias" class="ml-2 underline text-sm">Volver</a>
</div>
