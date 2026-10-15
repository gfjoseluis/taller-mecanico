{{-- Campos de proveedor compartidos (vista propia + modal Compra). Uso: @include("components.supplier-fields", ["p" => ""]) o ["p" => "s_"] --}}
@php($p = $p ?? '')
<x-field label="Nombre" hint="Ej: Repuestos El Alto SRL"><input wire:model="{{ $p }}nombre" placeholder="Ej: Repuestos El Alto SRL" class="border p-2 rounded w-full mt-1"></x-field>
@error($p.'nombre')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<x-field label="NIT" hint="Ej: 987654321"><input wire:model="{{ $p }}nit" placeholder="Ej: 987654321" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Teléfono" hint="Ej: 2821234"><input wire:model="{{ $p }}telefono" placeholder="Ej: 2821234" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Dirección" hint="Ej: Av. 6 de Marzo"><input wire:model="{{ $p }}direccion" placeholder="Ej: Av. 6 de Marzo" class="border p-2 rounded w-full mt-1"></x-field>
