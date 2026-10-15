<div class="bg-white p-4 rounded shadow max-w-xl">
<h2 class="font-bold mb-3">{{ $editId ? 'Editar' : 'Nuevo' }} proveedor</h2>
@include('components.supplier-fields', ['p' => ''])
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar</button>
<a href="/proveedores" class="ml-2 underline text-sm">Volver</a>
</div>
