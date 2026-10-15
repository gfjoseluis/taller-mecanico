@extends('layouts.admin')
@section('content')
<h1 class="text-xl font-bold mb-3 capitalize">{{ request()->path() }}</h1>
@if(session('ok'))<p class="bg-green-100 p-2 rounded mb-2">{{ session('ok') }}</p>@endif
<livewire:dynamic-component :component="$comp" />
@endsection
