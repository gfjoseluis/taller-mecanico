@props(['label', 'hint' => null, 'error' => null])
<label class="block text-sm font-medium">{{ $label }}
<span class="block mt-1">{{ $slot }}</span>
@if($hint)<span class="text-xs text-gray-500">{{ $hint }}</span>@endif
@if($error)<span class="text-xs text-red-600">{{ $error }}</span>@endif
</label>
