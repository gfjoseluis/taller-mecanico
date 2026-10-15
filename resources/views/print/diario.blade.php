<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Diario</title><style>body{font-family:sans-serif;font-size:12px}table{width:100%;border-collapse:collapse}td,th{border:1px solid #999;padding:4px}</style></head>
<body onload="window.print()">
<h1>Libro Diario — Taller Alto Tonelaje</h1>
<table><thead><tr><th>Fecha</th><th>Glosa</th><th>Módulo</th><th>Cuenta</th><th>Debe</th><th>Haber</th></tr></thead>
<tbody>@foreach($rows as $a)@foreach($a->lines as $l)<tr><td>{{ $a->fecha }}</td><td>{{ $a->glosa }}</td><td>{{ $a->modulo }}</td><td>{{ $l->account->codigo ?? '' }} {{ $l->account->nombre ?? '' }}</td><td>{{ $l->debe }}</td><td>{{ $l->haber }}</td></tr>@endforeach@endforeach</tbody></table>
</body></html>
