<div class="bg-white p-4 rounded shadow max-w-xl">
<h2 class="font-bold mb-1">Cobrar y cerrar — Orden {{ $orden->numero }}</h2>
<p class="text-sm text-gray-600 mb-2">{{ $orden->client->nombre ?? '' }} · {{ $orden->vehicle->placa ?? '' }}</p>
<p class="text-2xl mb-3">Total a cobrar: <strong>{{ number_format($orden->total, 2) }} Bs</strong></p>

<x-field label="¿Cómo paga?" hint="Efectivo, transferencia al banco o crédito (queda debiendo)">
<div class="grid grid-cols-3 gap-2 mt-1">
<label class="border p-2 rounded text-sm flex gap-2"><input type="radio" wire:model.live="medio" value="efectivo"> Efectivo (caja)</label>
<label class="border p-2 rounded text-sm flex gap-2"><input type="radio" wire:model.live="medio" value="transferencia"> Transferencia</label>
<label class="border p-2 rounded text-sm flex gap-2"><input type="radio" wire:model.live="medio" value="credito"> Crédito</label>
</div></x-field>

@if($medio === 'transferencia')
<x-field label="Banco destino" hint="Busca, la lista será larga"><livewire:searchable-select modelo="BankAccount" :value="$bank_account_id" placeholder="Ej: Caja chica, Banco Unión..." /></x-field>
@error('bank_account_id')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
@endif

@if($medio !== 'credito')
<x-field label="Monto recibido (Bs)" hint="Ej: 600.00"><input wire:model.live="recibido" type="number" step="0.01" min="0" placeholder="Ej: 600.00" class="border p-2 rounded w-full mt-1"></x-field>
@error('recibido')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<p class="text-lg mt-2">Vuelto: <strong>{{ number_format($this->vuelto, 2) }} Bs</strong></p>
@else
<p class="bg-yellow-50 border p-3 rounded text-sm mt-2">Queda como <strong>deuda del cliente: {{ number_format($orden->total, 2) }} Bs</strong> para cobrar después.</p>
@endif

<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Cobrar y cerrar</button>
<a href="/servicios" class="ml-2 underline text-sm">Volver</a>
</div>
