<div class="bg-white p-4 rounded shadow max-w-xl">
<h2 class="font-bold mb-3">{{ $editId ? 'Editar' : 'Nuevo' }} socio</h2>
<x-field label="Nombre" hint="Ej: Juan Pérez"><input wire:model="nombre" placeholder="Ej: Juan Pérez" class="border p-2 rounded w-full mt-1"></x-field>
@error('nombre')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<x-field label="CI" hint="Ej: 1234567 LP"><input wire:model="ci" placeholder="Ej: 1234567 LP" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Teléfono" hint="Ej: 70123456"><input wire:model="telefono" placeholder="Ej: 70123456" class="border p-2 rounded w-full mt-1"></x-field>
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar</button>
<a href="/socios" class="ml-2 underline text-sm">Volver</a>
</div>
