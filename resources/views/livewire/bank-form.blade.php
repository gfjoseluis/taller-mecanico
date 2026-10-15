<div class="bg-white p-4 rounded shadow max-w-xl">
<h2 class="font-bold mb-3">{{ $editId ? 'Editar' : 'Nueva' }} cuenta bancaria</h2>
<x-field label="Nombre corto" hint="Ej: Caja chica, Banco Unión CC"><input wire:model="nombre" placeholder="Ej: Banco Unión CC" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Banco" hint="Ej: Banco Unión, BNB, Mercantil, Efectivo"><input wire:model="banco" placeholder="Ej: Banco Unión" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Número" hint="Ej: 10000001"><input wire:model="numero" placeholder="Ej: 10000001" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Moneda"><select wire:model="moneda" class="border p-2 rounded w-full mt-1"><option>BOB</option><option>USD</option></select></x-field>
<x-field label="Saldo inicial" hint="Ej: 5000.00"><input wire:model="saldo" type="number" step="0.01" placeholder="Ej: 5000.00" class="border p-2 rounded w-full mt-1"></x-field>
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar</button>
<a href="/bancos" class="ml-2 underline text-sm">Volver</a>
</div>
