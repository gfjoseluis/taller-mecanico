<div class="bg-white p-4 rounded shadow max-w-2xl">
<h2 class="font-bold mb-3">{{ $editId ? 'Editar' : 'Nuevo' }} cliente</h2>
@include('components.client-fields', ['p' => ''])

<h3 class="font-bold mt-4 mb-1">Vehículos del cliente <span class="text-xs font-normal text-gray-500">(add vehicle — opcional, Ej: 1234-ABC)</span></h3>
<div class="grid grid-cols-6 gap-2 text-xs font-bold text-gray-600 px-1"><span>Placa*</span><span>Marca</span><span>Modelo</span><span>Chasis</span><span>Tonelaje</span><span></span></div>
@foreach($vehiculos as $i => $v)<div class="grid grid-cols-6 gap-2 mt-1">
<input wire:model="vehiculos.{{ $i }}.placa" placeholder="Ej: 1234-ABC" class="border p-2 rounded text-sm">
<input wire:model="vehiculos.{{ $i }}.marca" placeholder="Ej: Volvo" class="border p-2 rounded text-sm">
<input wire:model="vehiculos.{{ $i }}.modelo" placeholder="Ej: FH 540" class="border p-2 rounded text-sm">
<input wire:model="vehiculos.{{ $i }}.chasis" placeholder="Ej: 9BV..." class="border p-2 rounded text-sm">
<input wire:model="vehiculos.{{ $i }}.tonelaje" type="number" step="0.1" placeholder="Ej: 25.5" class="border p-2 rounded text-sm">
<button wire:click="quitarVehiculo({{ $i }})" class="text-red-600 text-sm">✕</button>
</div>@endforeach
@error('vehiculos')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
<button wire:click="addVehiculo" class="underline text-sm mt-2">+ Agregar vehículo</button>

<div class="mt-3"><button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded">Guardar</button>
<span wire:loading class="text-sm text-gray-500 ml-2">Guardando...</span>
<a href="/clientes" class="ml-2 underline text-sm">Volver</a></div>
</div>
