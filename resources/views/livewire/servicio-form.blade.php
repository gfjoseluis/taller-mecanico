<div class="bg-white p-4 rounded shadow w-full">
<h2 class="font-bold mb-1">{{ $editId ? 'Editar orden abierta' : 'Nueva orden de servicio' }}</h2>
<p class="text-xs text-gray-500 mb-3">Flujo cajero: 1 Cliente → 2 Vehículo del cliente → 3 Servicios + insumos</p>

<h3 class="font-bold text-sm mt-2">1) Cliente <span class="font-normal text-gray-500">(buscar o registrar)</span></h3>
<div class="flex gap-2 items-start">
<div class="flex-1"><livewire:searchable-select modelo="Client" :value="$client_id" :key="'cli-'.$client_id" placeholder="Ej: Transportes Andino, NIT 123..." /></div>
<x-accion wire:click="$set('modalCliente', true)">+ Registrar cliente</x-accion>
</div>
@error('client_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror

<h3 class="font-bold text-sm mt-4">2) Vehículo del cliente <span class="font-normal text-gray-500">(solo los suyos + agregar otro)</span></h3>
@if(! $client_id)
<p class="text-sm text-gray-500">Primero selecciona o registra al cliente. Ej: busca por nombre o NIT arriba.</p>
@elseif($vehiculosCliente->isEmpty() && ! $showAddVehiculo)
<p class="text-sm">Este cliente aún no tiene vehículos. <x-accion wire:click="$set('showAddVehiculo', true)">+ Agregar su primer vehículo</x-accion></p>
@else
<div class="space-y-1 mt-1">
@foreach($vehiculosCliente as $v)<label class="flex items-center gap-2 border p-2 rounded text-sm hover:bg-gray-50">
<input type="radio" wire:model.live="vehicle_id" value="{{ $v->id }}">
<span class="font-mono font-bold">{{ $v->placa }}</span><span>{{ $v->marca }} {{ $v->modelo }}</span>
</label>@endforeach
</div>
<x-accion wire:click="$set('showAddVehiculo', true)" class="mt-2">+ Agregar otro vehículo a este cliente (Ej: viene con otro camión)</x-accion>
@endif
@error('vehicle_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror

@if($showAddVehiculo && $client_id)<div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" wire:click.self="$set('showAddVehiculo', false)" x-on:keydown.escape.window="$wire.set('showAddVehiculo', false)"><div class="bg-white p-4 rounded w-full max-w-lg max-h-[90vh] overflow-auto relative">
<button wire:click="$set('showAddVehiculo', false)" aria-label="Cerrar" class="absolute top-2 right-3 text-xl text-gray-500 hover:text-black">✕</button>
<h4 class="font-bold mb-2">Agregar vehículo al cliente <span class="text-xs font-normal text-gray-500">(mismos campos que Vehículos → Nuevo)</span></h4>
@include('components.vehicle-fields', ['p' => 'v_'])
<div class="flex gap-2 mt-3"><button wire:click="guardarVehiculo" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded text-sm">Guardar vehículo</button><x-accion wire:click="$set('showAddVehiculo', false)">Cancelar</x-accion></div>
</div></div>@endif

<h3 class="font-bold text-sm mt-4">3) Servicios a realizar <span class="font-normal text-gray-500">(mantenimiento y/o reparaciones)</span></h3>
<x-field label="Diagnóstico general" hint="Ej: Mantenimiento 10.000 km + falla frenos"><textarea wire:model="diagnostico" placeholder="Ej: Mantenimiento 10.000 km..." class="border p-2 rounded w-full mt-1"></textarea></x-field>
<div class="grid grid-cols-4 gap-2 mt-2 text-xs font-bold text-gray-600 px-1"><span class="col-span-2">Servicio a realizar*</span><span>Precio (Bs)*</span><span></span></div>
@foreach($servicios as $i => $s)<div class="grid grid-cols-4 gap-2 mt-1">
<input wire:model="servicios.{{ $i }}.descripcion" placeholder="Ej: Cambio de aceite + filtro" aria-label="Servicio a realizar" class="border p-2 rounded text-sm col-span-2">
<input wire:model.live="servicios.{{ $i }}.precio" type="number" step="0.01" min="0" placeholder="Ej: 350.00" aria-label="Precio del servicio Bs" class="border p-2 rounded text-sm">
<button wire:click="quitarServicio({{ $i }})" class="text-red-600 text-sm">✕</button>
</div>@endforeach
<x-accion wire:click="addServicio" class="mt-2">+ Agregar servicio (Ej: alineado, frenos)</x-accion>

<label class="text-sm flex items-center gap-2 mt-4"><input type="checkbox" wire:model.live="usarInsumos"> ¿Usa repuestos/insumos? (Ej: filtros, aceites, pastillas)</label>
@if($usarInsumos)
<div class="grid grid-cols-5 gap-2 mt-2 text-xs font-bold text-gray-600 px-1"><span>Repuesto / Insumo*</span><span>Cantidad*</span><span>Precio aplicado (Bs)* — lo cobrado</span><span>Subtotal</span><span></span></div>
@foreach($lineas as $i => $l)<div class="grid grid-cols-5 gap-2 mt-1 items-center">
<div><livewire:searchable-select modelo="Product" :value="$l['product_id'] ?? null" :indice="$i" :key="'srv-prod-'.$i" placeholder="Ej: filtro aceite, SKU..." /></div>
<div><input wire:model.live="lineas.{{ $i }}.cantidad" type="number" step="0.01" min="0.01" aria-label="Cantidad" placeholder="Ej: 2" class="border p-2 rounded w-full text-sm"></div>
<div><input wire:model.live="lineas.{{ $i }}.precio" type="number" step="0.01" min="0" aria-label="Precio aplicado: lo cobrado al cliente (se autocompleta)" placeholder="Ej: 120.00 (auto)" class="border p-2 rounded w-full text-sm">
@if(! empty($l['product_id']) && isset($ventas[$l['product_id']]))<div class="text-[11px] text-gray-500">Sugerido ficha: {{ number_format($ventas[$l['product_id']], 2) }} · costo: {{ number_format($costos[$l['product_id']] ?? 0, 2) }}</div>@endif
@if(! empty($l['product_id']) && isset($costos[$l['product_id']]) && (float) ($l['precio'] ?? 0) > 0 && (float) ($l['precio'] ?? 0) < (float) $costos[$l['product_id']])<div class="text-[11px] text-red-600 font-bold">⚠ Vendes a pérdida (bajo el costo)</div>@endif</div>
<div><div class="text-sm text-right">{{ number_format((float) ($l['cantidad'] ?? 0) * (float) ($l['precio'] ?? 0), 2) }}</div>@if(! empty($l['product_id']) && isset($costos[$l['product_id']]))<div class="text-[11px] text-green-700 text-right">margen {{ number_format(((float) ($l['precio'] ?? 0) - (float) $costos[$l['product_id']]) * (float) ($l['cantidad'] ?? 0), 2) }}</div>@endif</div>
<div><x-accion wire:click="addLinea">+ línea</x-accion></div>
</div>@endforeach
@endif

<div class="bg-gray-50 rounded p-3 mt-4 text-sm">
<p>Servicios: <strong>{{ number_format($this->totalServicios, 2) }}</strong> + Insumos: <strong>{{ number_format($this->totalInsumos, 2) }}</strong></p>
</div>
<x-field label="Descuento (Bs)" hint="Ej: 135.00 preferente (opcional, resta del total)"><input wire:model.live.debounce.500ms="descuento" type="number" step="0.01" min="0" placeholder="Ej: 0.00" aria-label="Descuento en Bs" class="border p-2 rounded w-full mt-1"></x-field>
@error('descuento')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
@error('lineas')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<div class="bg-gray-50 rounded p-3 mt-2 text-sm">
<p>Subtotal: <strong>{{ number_format($this->subtotal, 2) }}</strong> − Descuento: <strong>{{ number_format((float) $this->descuento, 2) }}</strong></p>
<p class="text-lg">Total orden: <strong>{{ number_format($this->total, 2) }} Bs</strong></p>
</div>

<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Crear orden</button>
<span wire:loading class="text-sm text-gray-500 ml-2">Guardando...</span>
<x-accion href="/servicios">Volver</x-accion>

@if($modalCliente)<div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" wire:click.self="$set('modalCliente', false)" x-on:keydown.escape.window="$wire.set('modalCliente', false)"><div class="bg-white p-4 rounded w-full max-w-lg max-h-[90vh] overflow-auto relative">
<button wire:click="$set('modalCliente', false)" aria-label="Cerrar" class="absolute top-2 right-3 text-xl text-gray-500 hover:text-black">✕</button>
<h3 class="font-bold mb-2">Registrar cliente <span class="text-xs font-normal text-gray-500">(mismos campos que Clientes → Nuevo)</span></h3>
@include('components.client-fields', ['p' => 'c_'])
<div class="flex gap-2 mt-3"><button wire:click="guardarCliente" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded">Guardar</button><x-accion wire:click="$set('modalCliente', false)">Cerrar</x-accion></div>
</div></div>@endif
</div>
