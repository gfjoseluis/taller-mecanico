@props(['tono' => 'suave', 'href' => null])
@php($clases = [
    'suave' => 'border border-gray-400 text-gray-800 bg-white hover:bg-gray-100',
    'fuerte' => 'bg-slate-900 text-white hover:bg-slate-700 font-bold',
    'verde' => 'bg-taller text-white hover:opacity-90 font-bold',
    'peligro' => 'border border-red-400 text-red-700 bg-white hover:bg-red-50',
][$tono] ?? 'border border-gray-400 text-gray-800 bg-white hover:bg-gray-100')
@php($base = 'text-xs px-2 py-1 rounded whitespace-nowrap inline-block '.$clases)
@if($href)<a href="{{ $href }}" {{ $attributes->merge(['class' => $base]) }}>{{ $slot }}</a>
@else<button {{ $attributes->merge(['class' => $base]) }}>{{ $slot }}</button>@endif
