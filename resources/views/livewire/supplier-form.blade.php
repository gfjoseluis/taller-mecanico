<div class="bg-white p-4 rounded shadow w-full">
<h2 class="font-bold mb-3">{{ $editId ? 'Editar' : 'Nuevo' }} proveedor</h2>
@include('components.supplier-fields', ['p' => ''])
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar</button>
<x-accion href="/proveedores">Volver</x-accion>
</div>
