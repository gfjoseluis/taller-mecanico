<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Orden {{ $ot->numero }}</title><style>
@page{size:letter;margin:12mm 15mm}
body{font-family:sans-serif;font-size:12px;color:#000}
h1{font-size:20px;margin:0}h2{font-size:14px;margin:14px 0 4px;border-bottom:1px solid #000;padding-bottom:2px}
.muted{color:#444;font-size:11px}table{width:100%;border-collapse:collapse;margin-top:4px}
th,td{border:1px solid #666;padding:4px 6px;text-align:left}
td.num,th.num{text-align:right}.total{font-size:14px;margin-top:8px}
.grid{width:100%;margin-top:6px}.grid td{border:none;vertical-align:top;padding:0 8px 0 0}
.firmas{margin-top:28px;width:100%}.firmas td{border:none;text-align:center;padding-top:28px}
.firmas .linea{border-top:1px solid #000;margin:0 20px;padding-top:4px;font-size:11px}
.barra{background:#0f172a;color:#fff;padding:10px 15px;display:flex;gap:12px;align-items:center;margin-bottom:10px}
.barra a,.barra button{background:#fff;color:#0f172a;border:none;padding:6px 14px;border-radius:4px;font-size:14px;cursor:pointer;text-decoration:none}
@media print{.barra{display:none} thead{display:table-header-group} tr{page-break-inside:avoid} h2{page-break-after:avoid} .firmas{page-break-inside:avoid}}
</style></head><body onload="window.print()">
<div class="barra"><a href="/servicios">Volver</a><button onclick="window.print()">Imprimir</button><span>Orden {{ $ot->numero }} · tamaño carta</span></div>
@include('print._orden')
</body></html>
