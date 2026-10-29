<div class="bg-white p-4 rounded shadow w-full">
<h2 class="font-bold mb-1">{{ $editId ? 'Editar' : 'Nueva' }} pro-forma <span class="text-xs font-normal text-gray-500">(presupuesto: no mueve stock ni dinero)</span></h2>

<h3 class="font-bold text-sm mt-2">1) Cliente <span class="font-normal text-gray-500">(debe existir: créalo en Clientes)</span></h3>
<div class="flex-1"><livewire:searchable-select modelo="Client" :value="$client_id" :key="'pro-cli-'.$client_id" placeholder="Ej: Transportes Andino, NIT 123..." /></div>
@error('client_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
@error('vehicle_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror

<h3 class="font-bold text-sm mt-4">2) Vehículo del cliente</h3>
@if(! $client_id)
<p class="text-sm text-gray-500">Primero selecciona al cliente.</p>
@else
<div class="space-y-1 mt-1">
@foreach($vehiculosCliente as $v)<label class="flex items-center gap-2 border p-2 rounded text-sm hover:bg-gray-50">
<input type="radio" wire:model.live="vehicle_id" value="{{ $v->id }}">
<span class="font-mono font-bold">{{ $v->placa }}</span><span>{{ $v->marca }} {{ $v->modelo }}</span>
</label>@endforeach
</div>
@endif

<h3 class="font-bold text-sm mt-4">3) Servicios y repuestos cotizados</h3>
<x-field label="Detalle" hint="Ej: Reparación de embrague"><textarea wire:model="diagnostico" placeholder="Ej: Reparación de embrague..." class="border p-2 rounded w-full mt-1"></textarea></x-field>
<div class="grid grid-cols-4 gap-2 mt-2 text-xs font-bold text-gray-600 px-1"><span class="col-span-2">Servicio*</span><span>Precio (Bs)*</span><span></span></div>
@foreach($servicios as $i => $s)<div class="grid grid-cols-4 gap-2 mt-1">
<input wire:model="servicios.{{ $i }}.descripcion" placeholder="Ej: Cambio de disco" aria-label="Servicio cotizado" class="border p-2 rounded text-sm col-span-2">
<input wire:model.live="servicios.{{ $i }}.precio" type="number" step="0.01" min="0" placeholder="Ej: 650.00" aria-label="Precio del servicio Bs" class="border p-2 rounded text-sm">
<button wire:click="quitarServicio({{ $i }})" class="text-red-600 text-sm">✕</button>
</div>@endforeach
<button wire:click="addServicio" class="underline text-sm mt-2">+ Agregar servicio</button>

<div class="grid grid-cols-5 gap-2 mt-4 text-xs font-bold text-gray-600 px-1"><span>Repuesto / Insumo*</span><span>Cantidad*</span><span>Precio (Bs)*</span><span class="text-right">Subtotal</span><span></span></div>
@foreach($lineas as $i => $l)<div class="grid grid-cols-5 gap-2 mt-1 items-center">
<div><livewire:searchable-select modelo="Product" :value="$l['product_id'] ?? null" :indice="$i" :key="'pro-prod-'.$i" placeholder="Ej: filtro aceite, SKU..." /></div>
<div><input wire:model.live="lineas.{{ $i }}.cantidad" type="number" step="0.01" min="0.01" aria-label="Cantidad" placeholder="Ej: 2" class="border p-2 rounded w-full text-sm"></div>
<div><input wire:model.live="lineas.{{ $i }}.precio" type="number" step="0.01" min="0" aria-label="Precio Bs" placeholder="Ej: 120.00" class="border p-2 rounded w-full text-sm">
@if(! empty($l['product_id']) && isset($ventas[$l['product_id']]))<div class="text-[11px] text-gray-500">Lista: {{ number_format($ventas[$l['product_id']], 2) }}</div>@endif</div>
<div class="text-sm text-right">{{ number_format((float) ($l['cantidad'] ?? 0) * (float) ($l['precio'] ?? 0), 2) }}</div>
<div><button wire:click="addLinea" class="underline text-sm">+ línea</button></div>
</div>@endforeach
<button wire:click="addLinea" class="underline text-sm mt-2">+ Agregar línea</button>

<x-field label="Descuento (Bs)" hint="Opcional, resta del total"><input wire:model.live.debounce.500ms="descuento" type="number" step="0.01" min="0" placeholder="Ej: 0.00" aria-label="Descuento en Bs" class="border p-2 rounded w-full mt-1"></x-field>
@error('descuento')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<div class="bg-gray-50 rounded p-3 mt-2 text-sm">
<p>Subtotal: <strong>{{ number_format($this->subtotal, 2) }}</strong> − Descuento: <strong>{{ number_format((float) $this->descuento, 2) }}</strong></p>
<p class="text-lg">Total pro-forma: <strong>{{ number_format($this->total, 2) }} Bs</strong></p>
</div>

<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar pro-forma</button>
<x-accion href="/proformas">Volver</x-accion>
</div>
