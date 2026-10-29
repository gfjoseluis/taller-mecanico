<div class="bg-white p-4 rounded shadow">
<div class="flex justify-between mb-2"><h2 class="font-bold">Compras (insumos, repuestos, escritorio)</h2><a href="/compras/crear" class="bg-slate-900 text-white px-4 py-2 rounded text-sm">+ Nueva compra</a></div>
<div class="mb-2"><label for="buscar-compra" class="text-xs font-bold text-gray-600">Buscar compra</label><input id="buscar-compra" wire:model.live="search" placeholder="Ej: CMP-000001, importadora..." aria-label="Buscar compra por folio o proveedor" class="border p-2 rounded w-full text-sm mt-1"></div>
<table class="w-full text-sm">
    <thead>
        <tr class="text-left border-b">
            <th>Folio</th>
            <th>Proveedor</th>
            <th>Pago</th>
            <th>Total</th>
            <th>Saldo</th>
            <th>Estado</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $c)
            <tr class="border-b">
                <td class="font-mono">{{ $c->folio }}</td>
                <td>{{ $c->supplier->nombre ?? '' }}</td>
                <td>{{ $c->tipo_pago }}</td>
                <td>{{ number_format($c->total, 2) }}</td>
                <td>{{ number_format($c->saldo, 2) }}</td>
                <td><x-estado :texto="$c->estado" /></td>
                <td>
                    <x-accion :href="'/compras/'.$c->id">Ver</x-accion>
                    <x-accion tono="fuerte" :href="'/compras/'.$c->id.'/imprimir'" target="_blank">Imprimir</x-accion>
                    @if($c->estado === 'vigente' && (float) $c->saldo > 0)
                        <x-accion tono="verde" :href="'/por-pagar/'.$c->id.'/abonar'">Abonar</x-accion>
                    @endif
                    @if($c->estado === 'vigente')
                        <x-accion tono="peligro" @click="confirmarLive($wire, 'anular', [{{ $c->id }}], 'Anular compra', 'Devuelve stock y revierte el dinero. ¿Seguro?')">Anular</x-accion>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="mt-2">{{ $items->links() }}</div>
</div>
