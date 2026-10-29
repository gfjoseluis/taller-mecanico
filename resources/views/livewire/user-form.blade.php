<div class="bg-white p-4 rounded shadow w-full">
<h2 class="font-bold mb-3">{{ $editId ? 'Editar' : 'Nuevo' }} usuario</h2>
<x-field label="Nombre" hint="Ej: Juan Pérez"><input wire:model="name" placeholder="Ej: Juan Pérez" class="border p-2 rounded w-full mt-1"></x-field>
@error('name')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<x-field label="Email" hint="Ej: juan@taller.bo (acceso al sistema)"><input wire:model="email" placeholder="Ej: juan@taller.bo" class="border p-2 rounded w-full mt-1"></x-field>
<x-field label="Contraseña" hint="Ej: Taller123* (vacío = no cambiar al editar)"><input wire:model="password" type="password" placeholder="Ej: Taller123*" class="border p-2 rounded w-full mt-1"></x-field>
@error('password')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
<h3 class="font-bold mt-3 text-sm">Roles</h3>
@foreach($todosRoles as $r)<label class="flex gap-2 text-sm mt-1"><input type="checkbox" wire:model="roles" value="{{ $r->name }}"> {{ $r->name }}</label>@endforeach
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar</button>
<x-accion href="/usuarios">Volver</x-accion>
</div>
