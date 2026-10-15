@extends('layouts.admin')
@section('content')
<div class="bg-white p-8 rounded shadow max-w-md mx-auto text-center">
<h1 class="font-display text-4xl font-semibold">Sin permiso</h1>
<p class="text-sm text-gray-600 mt-2">Tu usuario no puede abrir esta parte. Pide acceso al encargado o al administrador.</p>
<a href="/dashboard" class="underline text-sm mt-4 inline-block">Volver al tablero</a>
</div>
@endsection
