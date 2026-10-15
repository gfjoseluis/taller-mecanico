<div class="bg-white p-4 rounded shadow max-w-3xl">
<h2 class="font-bold mb-3">Nueva compra</h2>
<livewire:searchable-select modelo="Supplier" :value="$supplier_id" :key="'sup-'.$supplier_id" placeholder="Ej: Repuestos El Alto, NIT..." />
<button wire:click="$set('modalProveedor', true)" class="text-xs underline mt-1">+ Registrar importadora (mismos campos que Proveedores → Nuevo)</button>
<x-field label="Tipo pago" hint="Crédito genera CxP (saldo pendiente)"><select wire:model="tipo_pago" class="border p-2 rounded w-full mt-1"><option value="contado">Contado</option><option value="credito">Crédito por repuestos</option></select></x-field>
<x-field label="Cuenta banco (si contado)" hint="Ej: Banco Unión CC"><livewire:searchable-select modelo="BankAccount" :value="$bank_account_id" placeholder="Ej: Caja chica, Banco Unión..." /></x-field>
<x-field label="Glosa" hint="Ej: Compra filtros y aceites. Este costo actualiza el producto (no repite su ficha)"><input wire:model="glosa" placeholder="Ej: Compra filtros y aceites" class="border p-2 rounded w-full mt-1"></x-field>
<h3 class="font-bold mt-3">Líneas de compra <span class="text-xs font-normal text-gray-500">(repuesto/insumo + cantidad + costo)</span></h3>
<div class="grid grid-cols-4 gap-2 mt-2 text-xs font-bold text-gray-600 px-1"><span>Repuesto / Insumo*</span><span>Cantidad*</span><span>Costo unitario (Bs)* — lo pagado al proveedor</span><span></span></div>
@foreach($lineas as $i => $l)<div class="grid grid-cols-4 gap-2 mt-1">
<livewire:searchable-select modelo="Product" :value="$l['product_id'] ?? null" :indice="$i" :key="'cmp-prod-'.$i" placeholder="Ej: filtro aceite, SKU..." />
<input wire:model="lineas.{{ $i }}.cantidad" type="number" step="0.01" aria-label="Cantidad" placeholder="Ej: 5" class="border p-2 rounded text-sm">
<input wire:model="lineas.{{ $i }}.costo" type="number" step="0.01" aria-label="Costo unitario Bs" placeholder="Ej: 80.00" class="border p-2 rounded text-sm">
<button wire:click="addLinea" class="underline text-sm">+ línea</button>
</div>@endforeach
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar compra</button>
<a href="/compras" class="ml-2 underline text-sm">Volver</a>

@if($modalProveedor)<div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" wire:click.self="$set('modalProveedor', false)" x-on:keydown.escape.window="$wire.set('modalProveedor', false)"><div class="bg-white p-4 rounded w-full max-w-lg max-h-[90vh] overflow-auto relative">
<button wire:click="$set('modalProveedor', false)" aria-label="Cerrar" class="absolute top-2 right-3 text-xl text-gray-500 hover:text-black">✕</button>
<h3 class="font-bold mb-2">Registrar importadora <span class="text-xs font-normal text-gray-500">(mismos campos que Proveedores → Nuevo)</span></h3>
@include('components.supplier-fields', ['p' => 's_'])
<div class="flex gap-2 mt-3"><button wire:click="guardarProveedor" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded">Guardar</button><button wire:click="$set('modalProveedor', false)" class="underline text-sm">Cerrar</button></div>
</div></div>@endif
</div>
