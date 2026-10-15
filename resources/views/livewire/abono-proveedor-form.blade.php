<div class="bg-white p-4 rounded shadow max-w-xl">
<h2 class="font-bold mb-1">Abono — Compra {{ $compra->folio }}</h2>
<p class="text-sm text-gray-600 mb-2">{{ $compra->supplier->nombre ?? '' }}</p>
<p class="text-2xl mb-3">Deuda pendiente: <strong>{{ number_format($compra->saldo, 2) }} Bs</strong></p>
<x-field label="¿Cómo paga?" hint="Efectivo o transferencia al banco"><div class="grid grid-cols-2 gap-2 mt-1">
<label class="border p-2 rounded text-sm flex gap-2"><input type="radio" wire:model.live="medio" value="efectivo"> Efectivo (caja)</label>
<label class="border p-2 rounded text-sm flex gap-2"><input type="radio" wire:model.live="medio" value="transferencia"> Transferencia</label>
</div></x-field>
@if($medio === 'transferencia')
<x-field label="Banco origen" hint="Busca, la lista será larga"><livewire:searchable-select modelo="BankAccount" :value="$bank_account_id" placeholder="Ej: Caja chica, Banco Unión..." /></x-field>
@error('bank_account_id')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
@endif
<x-field label="Monto del abono (Bs)" hint="Ej: 200.00 (máximo la deuda)"><input wire:model.live="monto" type="number" step="0.01" min="0.01" placeholder="Ej: 200.00" class="border p-2 rounded w-full mt-1"></x-field>
@error('monto')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Registrar abono</button>
<a href="/por-pagar" class="ml-2 underline text-sm">Volver</a>
</div>
