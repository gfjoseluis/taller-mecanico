<div class="bg-white p-4 rounded shadow w-full">
<h2 class="font-bold mb-1">Recepción — Orden {{ $orden->numero }}</h2>
<p class="text-sm text-gray-600 mb-2">{{ $orden->client->nombre ?? '' }} · {{ $orden->vehicle->placa ?? '' }}</p>
<p class="text-xs text-gray-500 mb-3">Cómo llega el vehículo. Marca lo que está bien; lo demás se detalla en observaciones.</p>

<x-field label="Kilometraje al ingresar" hint="Odómetro del tablero (ej: 71158)"><input wire:model="kilometraje" type="number" step="1" min="0" placeholder="Ej: 71158" aria-label="Kilometraje al ingresar" class="border p-2 rounded w-full mt-1"></x-field>
@error('kilometraje')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror

<x-field label="Combustible" hint="Nivel al ingresar (toca uno)">
<div class="grid grid-cols-5 gap-2 mt-1" role="radiogroup" aria-label="Nivel de combustible">
@foreach($combOpciones as $k => $l)<button type="button" wire:click="$set('combustible', '{{ $k }}')" aria-pressed="{{ $combustible === $k ? 'true' : 'false' }}" class="border p-2 rounded text-sm font-bold {{ $combustible === $k ? 'bg-slate-900 text-white' : 'bg-white hover:bg-gray-100' }}">{{ $l }}</button>@endforeach
</div></x-field>
@error('combustible')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror

<h3 class="font-bold text-sm mt-4">Inventario del vehículo</h3>
<div class="grid grid-cols-2 gap-1 mt-1">
@foreach($invItems as $k => $l)<label class="flex items-center gap-2 border p-2 rounded text-sm"><input type="checkbox" wire:model="inventario" value="{{ $k }}"> {{ $l }}</label>@endforeach
</div>
<x-field label="Otros (inventario)" hint="Ej: tapa cubos, herramientas"><input wire:model="inventario_otros" placeholder="Ej: caja de herramientas" aria-label="Otros del inventario" class="border p-2 rounded w-full mt-1"></x-field>

<h3 class="font-bold text-sm mt-4">Inspección de ingreso</h3>
<div class="grid grid-cols-2 gap-1 mt-1">
@foreach($insItems as $k => $l)<label class="flex items-center gap-2 border p-2 rounded text-sm"><input type="checkbox" wire:model="inspeccion" value="{{ $k }}"> {{ $l }} OK</label>@endforeach
</div>
<x-field label="Observaciones" hint="Rayones, golpes, faltantes"><textarea wire:model="observaciones" placeholder="Ej: rayón puerta izquierda..." aria-label="Observaciones de recepción" class="border p-2 rounded w-full mt-1"></textarea></x-field>

<h3 class="font-bold text-sm mt-4">Fotos del vehículo <span class="font-normal text-gray-500">(guía + subir o tomar foto)</span></h3>
@if(session('ok'))<p class="bg-green-100 p-2 rounded my-2 text-sm">{{ session('ok') }}</p>@endif
@error('subidas')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
@error('subidas.*')<p class="text-red-600 text-xs">Archivo inválido: usa JPG/PNG hasta 5 MB.</p>@enderror
<div class="grid grid-cols-5 gap-2 mt-1">
@foreach($posiciones as $k => $l)<div class="border rounded p-2 text-center text-sm">
<p class="font-bold text-xs">{{ $l }}</p>
@if(empty($fotos[$k]))
<img src="/plantillas/{{ $guias[$k] }}" alt="Guía {{ $l }}" class="w-16 h-16 object-contain bg-gray-50 rounded my-1 mx-auto">
<label class="block mt-1 text-xs underline cursor-pointer">Subir / foto<input type="file" x-on:change="$wire.upload('subidas.{{ $k }}', $event.target.files[0], () => $wire.call('procesarFoto', '{{ $k }}'))" accept="image/*" capture="environment" aria-label="Subir foto {{ $l }}" class="hidden"></label>
@else
<img src="/storage/{{ $fotos[$k] }}" alt="Foto {{ $l }}" class="w-16 h-16 object-cover rounded my-1 mx-auto">
<p class="text-xs text-green-700">Guardada ✓</p>
<label class="block mt-1 text-xs underline cursor-pointer">Cambiar<input type="file" x-on:change="$wire.upload('subidas.{{ $k }}', $event.target.files[0], () => $wire.call('procesarFoto', '{{ $k }}'))" accept="image/*" capture="environment" aria-label="Cambiar foto {{ $l }}" class="hidden"></label>
@endif
<div wire:loading wire:target="subidas" class="text-xs text-gray-500">Subiendo...</div>
</div>@endforeach
</div>

<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar recepción</button>
<x-accion :href="'/servicios/'.$orden->id">Volver</x-accion>
</div>
