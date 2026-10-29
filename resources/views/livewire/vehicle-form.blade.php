<div class="bg-white p-4 rounded shadow w-full">
<h2 class="font-bold mb-3">{{ $editId ? 'Editar' : 'Nuevo' }} vehículo alto tonelaje</h2>
<x-field label="Cliente" hint="Busca por nombre o NIT (buscador, sin scroll)"><livewire:searchable-select modelo="Client" :value="$client_id" placeholder="Ej: Transportes Andino, NIT..." /></x-field>
<x-field label="Placa" hint="Ej: 1234-ABC"><input wire:model="placa" placeholder="Ej: 1234-ABC" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Marca" hint="Ej: Volvo, Scania, Mercedes"><input wire:model="marca" placeholder="Ej: Volvo" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Modelo" hint="Ej: FH 540"><input wire:model="modelo" placeholder="Ej: FH 540" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Chasis" hint="Ej: 9BV123456789"><input wire:model="chasis" placeholder="Ej: 9BV123456789" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Tonelaje" hint="Ej: 25.5"><input wire:model="tonelaje" type="number" step="0.1" placeholder="Ej: 25.5" class="border p-2 rounded w-full mt-1"></x-field>
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar</button>
<x-accion href="/vehiculos">Volver</x-accion>
</div>
