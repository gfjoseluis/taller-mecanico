{{-- Campos de vehículo compartidos. Uso: @include("components.vehicle-fields", ["p" => ""]) o ["p" => "v_"] --}}
@php($p = $p ?? '')
<x-field label="Placa" hint="Ej: 1234-ABC"><input wire:model="{{ $p }}placa" placeholder="Ej: 1234-ABC" class="border p-2 rounded w-full mt-1"></x-field>
@error($p.'placa')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<x-field label="Marca" hint="Ej: Volvo, Scania"><input wire:model="{{ $p }}marca" placeholder="Ej: Volvo" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Modelo" hint="Ej: FH 540"><input wire:model="{{ $p }}modelo" placeholder="Ej: FH 540" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Chasis" hint="Ej: 9BV123456789"><input wire:model="{{ $p }}chasis" placeholder="Ej: 9BV123456789" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Tonelaje" hint="Ej: 25.5"><input wire:model="{{ $p }}tonelaje" type="number" step="0.1" placeholder="Ej: 25.5" class="border p-2 rounded w-full mt-1"></x-field>
