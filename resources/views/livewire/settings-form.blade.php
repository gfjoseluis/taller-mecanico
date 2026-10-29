<div class="bg-white p-4 rounded shadow w-full">
@if(session('ok'))<p class="bg-green-100 p-2 rounded mb-2">{{ session('ok') }}</p>@endif
<h2 class="font-bold mb-2">Configuración (gateway SIAT, impresoras, empresa)</h2>
@foreach($form as $k => $v)<label class="block text-sm mt-2">{{ $k }}<input wire:model="form.{{ $k }}" class="border p-2 rounded w-full mt-1"></label>@endforeach
<button wire:click="guardar" wire:loading.attr="disabled" class="bg-slate-900 text-white px-4 py-2 rounded mt-3">Guardar</button>
<p class="text-xs text-gray-500 mt-2">Driver SIAT: mock | http. Al cambiar a http usa base_url + token del proveedor.</p>
</div>
