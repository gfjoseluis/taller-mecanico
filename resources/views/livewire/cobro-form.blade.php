<div class="bg-white p-4 rounded shadow w-full">
<h2 class="font-bold mb-1">Cobrar — Orden {{ $orden->numero }}</h2>
<p class="text-sm text-gray-600 mb-2">{{ $orden->client->nombre ?? '' }} · {{ $orden->vehicle->placa ?? '' }}</p>
<div class="bg-gray-50 rounded p-3 text-2xl mb-3">
<p>Total: <strong>{{ number_format($orden->total, 2) }}</strong> · Ya dio: <strong>{{ number_format($this->yaDio, 2) }}</strong> · Falta: <strong>{{ number_format($this->falta, 2) }} Bs</strong></p>
@if((float) $orden->descuento > 0)<p class="text-sm text-gray-600">Incluye descuento de {{ number_format($orden->descuento, 2) }} Bs.</p>@endif
</div>

<x-field label="¿Cómo paga?" hint="Efectivo a caja o transferencia al banco">
<div class="grid grid-cols-2 gap-2 mt-1">
<label class="border p-2 rounded text-sm flex gap-2"><input type="radio" wire:model.live="medio" value="efectivo"> Efectivo (caja)</label>
<label class="border p-2 rounded text-sm flex gap-2"><input type="radio" wire:model.live="medio" value="transferencia"> Transferencia</label>
</div></x-field>

@if($medio === 'transferencia')
<x-field label="Banco destino" hint="Busca, la lista será larga"><livewire:searchable-select modelo="BankAccount" :value="$bank_account_id" placeholder="Ej: Caja chica, Banco Unión..." /></x-field>
@error('bank_account_id')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
@endif

<x-field label="Recibe ahora (Bs)" hint="Si es menos de lo que falta, queda como adelanto y la orden sigue abierta"><input wire:model.live="recibido" type="number" step="0.01" min="0" placeholder="Ej: 300.00" aria-label="Cuánto dinero recibe ahora en Bs" class="border p-2 rounded w-full mt-1"></x-field>
@error('recibido')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
@if($this->recibido >= $this->falta && $this->falta > 0)
<p class="text-lg mt-2">Vuelto: <strong>{{ number_format($this->vuelto, 2) }} Bs</strong> · Se cierra la orden.</p>
@elseif($this->recibido > 0)
<p class="text-sm mt-2 text-blue-700">Queda como adelanto. Faltarían {{ number_format($this->falta - $this->recibido, 2) }} Bs.</p>
@endif

<div class="flex gap-2 items-center mt-3">
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded">{{ $this->falta > 0 ? 'Cobrar' : 'Cerrar orden' }}</button>
@if($this->falta > 0)<x-accion wire:click="quedaDebiendo" wire:loading.attr="disabled">Queda debiendo ({{ number_format($this->falta, 2) }} Bs)</x-accion>@endif
<x-accion href="/servicios">Volver</x-accion>
</div>
</div>
