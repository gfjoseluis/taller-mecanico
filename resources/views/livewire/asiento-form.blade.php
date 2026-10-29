<div class="bg-white p-4 rounded shadow w-full">
<h2 class="font-bold mb-3">Registro manual (contador)</h2>
<x-field label="Fecha" hint="Ej: hoy; mes cerrado se bloquea"><input wire:model="fecha" type="date" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Detalle" hint="Ej: Depreciación enero"><input wire:model="glosa" placeholder="Ej: Depreciación enero" class="border p-2 rounded w-full mt-1"></x-field>
<div class="grid grid-cols-4 gap-2 mt-2 text-xs font-bold text-gray-600 px-1"><span>Cuenta*</span><span>Debe</span><span>Haber</span><span></span></div>
@foreach($lineas as $i => $l)<div class="grid grid-cols-4 gap-2 mt-1">
<livewire:searchable-select modelo="ChartAccount" :value="$l['account_id'] ?? null" :indice="$i" :key="'asi-cta-'.$i" placeholder="Ej: 5.2 gastos..." />
<input wire:model.live="lineas.{{ $i }}.debe" type="number" step="0.01" min="0" aria-label="Debe" placeholder="Ej: 100.00" class="border p-2 rounded text-sm">
<input wire:model.live="lineas.{{ $i }}.haber" type="number" step="0.01" min="0" aria-label="Haber" placeholder="Ej: 100.00" class="border p-2 rounded text-sm">
<x-accion wire:click="addLinea">+ línea</x-accion>
</div>@endforeach
@error('lineas')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar (debe = haber)</button>
<x-accion href="/contabilidad">Volver</x-accion>
</div>
