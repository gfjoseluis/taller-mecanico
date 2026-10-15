<div class="bg-white p-4 rounded shadow max-w-xl">
<h2 class="font-bold mb-3">Nuevo movimiento de dinero</h2>
<x-field label="Tipo" hint="Gasto resta · Entrada suma · Traspaso mueve entre cuentas · Aporte/Retiro es dinero de socios"><select wire:model.live="tipo" class="border p-2 rounded w-full mt-1"><option value="salida">Gasto / salida (Ej: materiales, pago crédito)</option><option value="entrada">Entrada / ajuste (Ej: corrección)</option><option value="traspaso">Traspaso entre cuentas</option><option value="aporte">Aporte de socio (Ej: socio mete 50.000)</option><option value="retiro">Retiro de socio (Ej: socio saca dinero)</option></select></x-field>
<x-field label="Cuenta" hint="De dónde sale (o entra) el dinero"><livewire:searchable-select modelo="BankAccount" :value="$cuenta_id" :indice="'origen'" placeholder="Ej: Caja chica, Banco Unión..." /></x-field>
@if($tipo === 'traspaso')
<x-field label="Cuenta destino" hint="A dónde llega el dinero"><livewire:searchable-select modelo="BankAccount" :value="$cuenta_destino_id" :indice="'destino'" placeholder="Ej: Banco BNB..." /></x-field>
@error('cuenta_destino_id')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
@endif
@if($tipo === 'salida' || $tipo === 'entrada')
<x-field label="Cuenta contable contrapartida" hint="Ej: gastos operativos (salida) o servicios (entrada)"><livewire:searchable-select modelo="ChartAccount" :value="$contra_id" placeholder="Ej: 5.2 gastos, 4.2 servicios..." /></x-field>
@error('contra_id')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
@endif
@if($tipo === 'aporte' || $tipo === 'retiro')
<x-field label="Socio" hint="Dueño que aporta o retira"><select wire:model="partner_id" class="border p-2 rounded w-full mt-1"><option value="">Seleccionar... Ej: Juan Pérez</option>@foreach($socios as $s)<option value="{{ $s->id }}">{{ $s->nombre }}</option>@endforeach</select></x-field>
@error('partner_id')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
@endif
<x-field label="Monto (Bs)" hint="Ej: 1200.00"><input wire:model="monto" type="number" step="0.01" min="0.01" placeholder="Ej: 1200.00" class="border p-2 rounded w-full mt-1"></x-field>
@error('monto')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<x-field label="Motivo" hint="Ej: Pago crédito repuestos, materiales escritorio"><input wire:model="motivo" placeholder="Ej: Pago crédito repuestos" class="border p-2 rounded w-full mt-1"></x-field>
@error('motivo')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar movimiento</button>
<a href="/movimientos" class="ml-2 underline text-sm">Volver</a>
</div>
