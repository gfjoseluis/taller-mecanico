<div class="relative">
@if($value && $label)
<div class="flex items-center gap-2 border p-2 rounded bg-green-50"><span class="text-sm font-medium">{{ $label }}</span><button wire:click="limpiar" class="text-xs underline">Cambiar</button></div>
@else
<x-field :label="$modelo" :hint="'Escribe mínimo 2 letras. Ej: ' . $placeholder">
<input wire:model.live.debounce.300ms="query" placeholder="{{ $placeholder }}" class="border p-2 rounded w-full mt-1">
</x-field>
@if(count($resultados))
<ul class="absolute z-10 bg-white border rounded w-full max-h-48 overflow-auto shadow">
@foreach($resultados as $r)<li><button wire:click="elegir({{ $r['id'] }}, '{{ addslashes($r['texto']) }}')" class="w-full text-left px-3 py-2 hover:bg-gray-100 text-sm">{{ $r['texto'] }}</button></li>@endforeach
</ul>
@endif
@endif
</div>
