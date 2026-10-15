{{-- Campos de cliente compartidos (vista propia + modal Servicio). Uso: @include("components.client-fields", ["p" => ""]) o ["p" => "c_"] --}}
@php($p = $p ?? '')
<x-field label="Nombre / Razón social" hint="Ej: Transportes Andino SRL"><input wire:model="{{ $p }}nombre" placeholder="Ej: Transportes Andino SRL" class="border p-2 rounded w-full mt-1"></x-field>
@error($p.'nombre')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<x-field label="NIT / CI" hint="Ej: 123456789 (obligatorio SIAT)"><input wire:model="{{ $p }}nit_ci" placeholder="Ej: 123456789" class="border p-2 rounded w-full mt-1"></x-field>
@error($p.'nit_ci')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<x-field label="Teléfono" hint="Ej: 70123456"><input wire:model="{{ $p }}telefono" placeholder="Ej: 70123456" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Email" hint="Ej: cliente@empresa.bo"><input wire:model="{{ $p }}email" placeholder="Ej: cliente@empresa.bo" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Dirección" hint="Ej: Av. Blanco Galindo Km 5"><input wire:model="{{ $p }}direccion" placeholder="Ej: Av. Blanco Galindo Km 5" class="border p-2 rounded w-full mt-1"></x-field>
