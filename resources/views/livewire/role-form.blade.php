<div class="bg-white p-4 rounded shadow w-full">
<h2 class="font-bold mb-3">{{ $editId ? 'Editar' : 'Nuevo' }} rol</h2>
<x-field label="Nombre rol" hint="Ej: mecanico, caja"><input wire:model="name" placeholder="Ej: mecanico" class="border p-2 rounded w-full mt-1"></x-field>
<h3 class="font-bold mt-3 text-sm">Permisos por acción</h3>
<div class="grid md:grid-cols-3 gap-1 mt-2">@foreach($todos as $p)<label class="flex gap-2 text-xs"><input type="checkbox" wire:model="perms" value="{{ $p->name }}"> {{ $p->name }}</label>@endforeach</div>
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar</button>
<x-accion href="/roles">Volver</x-accion>
</div>
