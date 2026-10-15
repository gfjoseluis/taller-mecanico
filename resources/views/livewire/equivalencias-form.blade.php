<div class="bg-white p-4 rounded shadow max-w-2xl">
<h2 class="font-bold mb-1">Equivalencias contables</h2>
<p class="text-xs text-gray-500 mb-3">Qué cuenta usa cada concepto. Si una queda mal, diario, mayor y balance salen mal juntos — revisar una vez con el contador.</p>
@if(session('ok'))<p class="bg-green-100 p-2 rounded mb-2">{{ session('ok') }}</p>@endif
@foreach($conceptos as $k => $label)<x-field :label="$label" :hint="'Código actual: ' . ($mapa[$k] ?? '')"><select wire:model="mapa.{{ $k }}" class="border p-2 rounded w-full mt-1">@foreach($cuentas as $c)<option value="{{ $c->codigo }}">{{ $c->codigo }} — {{ $c->nombre }}</option>@endforeach</select></x-field>@endforeach
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar equivalencias</button>
</div>
