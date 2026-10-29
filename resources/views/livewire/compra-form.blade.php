<div class="bg-white p-4 rounded shadow w-full">
<h2 class="font-bold mb-3">Nueva compra</h2>
<x-field label="Proveedor" hint="Opcional al contado (ferretería, mercado). Obligatorio a crédito"><livewire:searchable-select modelo="Supplier" :value="$supplier_id" :key="'sup-'.$supplier_id" placeholder="Ej: Repuestos El Alto, NIT... (vacío = compra local)" /></x-field>
@error('supplier_id')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<x-accion wire:click="$set('modalProveedor', true)" class="mt-1">+ Registrar importadora (mismos campos que Proveedores → Nuevo)</x-accion>
<x-field label="Tipo pago" hint="A crédito queda debiendo (saldo pendiente)"><select wire:model="tipo_pago" class="border p-2 rounded w-full mt-1"><option value="contado">Contado</option><option value="credito">Crédito por repuestos</option></select></x-field>
<x-field label="Cuenta banco (si contado)" hint="Ej: Banco Unión CC"><livewire:searchable-select modelo="BankAccount" :value="$bank_account_id" placeholder="Ej: Caja chica, Banco Unión..." /></x-field>
<x-field label="Glosa" hint="Ej: Compra filtros y aceites. Este costo actualiza el producto (no repite su ficha)"><input wire:model="glosa" placeholder="Ej: Compra filtros y aceites (obligatorio si no hay proveedor)" class="border p-2 rounded w-full mt-1"></x-field>
@error('glosa')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<h3 class="font-bold mt-3">Líneas de compra <span class="text-xs font-normal text-gray-500">(repuesto/insumo + cantidad + costo)</span></h3>
<div class="grid grid-cols-5 gap-2 mt-2 text-xs font-bold text-gray-600 px-1"><span>Repuesto / Insumo*</span><span>Cantidad*</span><span>Costo unitario (Bs)* — lo pagado al proveedor</span><span class="text-right">Subtotal</span><span></span></div>
@foreach($lineas as $i => $l)<div class="grid grid-cols-5 gap-2 mt-1 items-center">
<livewire:searchable-select modelo="Product" :value="$l['product_id'] ?? null" :indice="$i" :key="'cmp-prod-'.$i" placeholder="Ej: filtro aceite, SKU..." />
<input wire:model.live="lineas.{{ $i }}.cantidad" type="number" step="0.01" aria-label="Cantidad" placeholder="Ej: 5" class="border p-2 rounded text-sm">
<input wire:model.live="lineas.{{ $i }}.costo" type="number" step="0.01" aria-label="Costo unitario Bs" placeholder="Ej: 80.00" class="border p-2 rounded text-sm">
<div class="text-sm text-right">{{ number_format((float) ($l['cantidad'] ?? 0) * (float) ($l['costo'] ?? 0), 2) }}</div>
<x-accion wire:click="addLinea">+ línea</x-accion>
</div>@endforeach
<div class="bg-gray-50 rounded p-3 mt-3 text-sm">
<p class="text-lg">Total compra: <strong>{{ number_format($this->total, 2) }} Bs</strong></p>
</div>
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar compra</button>
<x-accion href="/compras">Volver</x-accion>

@if($modalProveedor)<div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" wire:click.self="$set('modalProveedor', false)" x-on:keydown.escape.window="$wire.set('modalProveedor', false)"><div class="bg-white p-4 rounded w-full max-w-lg max-h-[90vh] overflow-auto relative">
<button wire:click="$set('modalProveedor', false)" aria-label="Cerrar" class="absolute top-2 right-3 text-xl text-gray-500 hover:text-black">✕</button>
<h3 class="font-bold mb-2">Registrar importadora <span class="text-xs font-normal text-gray-500">(mismos campos que Proveedores → Nuevo)</span></h3>
@include('components.supplier-fields', ['p' => 's_'])
<div class="flex gap-2 mt-3"><button wire:click="guardarProveedor" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded">Guardar</button><x-accion wire:click="$set('modalProveedor', false)">Cerrar</x-accion></div>
</div></div>@endif
</div>
