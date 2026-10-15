@extends('layouts.admin')
@section('content')
<h1 class="text-xl font-bold mb-3">{{ $titulo ?? 'Formulario' }}</h1>
@if(session('ok'))<p class="bg-green-100 p-2 rounded mb-2">{{ session('ok') }}</p>@endif
@if(isset($id))
<livewire:dynamic-component :component="$comp" :id="$id" />
@else
<livewire:dynamic-component :component="$comp" />
@endif
@endsection
