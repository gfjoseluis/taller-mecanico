# Manual de uso — Taller Alto Tonelaje

Lenguaje simple para el día a día. Lo técnico (instalación) está en `README.md`.

## Glosario corto

- **Total**: lo que cuesta el trabajo completo.
- **Ya dio**: lo adelantado por el cliente.
- **Falta**: lo que queda por cobrar (total menos ya dio).
- **Queda debiendo (crédito)**: se llevó el vehículo sin pagar todo; la deuda se cobra en Por cobrar.
- **Costo**: lo pagado al proveedor (lo pone la compra sola).
- **Precio de venta**: lo cobrado al cliente.
- **Vigente / Pagada**: compra con deuda / compra saldada.
- **Abierta / Cobrada / Pendiente / Anulada**: orden en taller / cerrada pagada /
  cerrada con deuda / anulada (devuelve stock).

## Qué nunca hacer

1. Una orden cerrada no se edita: se abre una orden nueva.
2. Montos a mano en fichas: el dinero solo se mueve con cobros, abonos y movimientos.
3. Ninguna cuenta puede quedar en negativo: el sistema bloquea la salida.
4. El stock solo entra por compras; el producto nuevo nace en 0.
5. Restaurar pide `RESTAURAR` + tu clave y detiene el sistema 1 minuto: avisa antes.

## Flujo 1: Servicio (el diario de caja)

1. **Servicio → Órdenes → Nueva orden**: 1 Cliente (buscar o registrar),
   2 Vehículo del cliente (elegir o agregar), 3 Servicios + insumos.
   El total se ve en vivo. Guardar.
2. **Cobrar** (una sola pantalla): muestra Total, Ya dio y Falta.
   - Recibe lo que falta o más → **Cobrar**: cierra con vuelto si sobra.
   - Recibe menos → queda como **adelanto**, la orden sigue abierta.
   - No paga ahora → **Queda debiendo**: cierra a crédito por lo que falta.
3. **Imprimir** abre la hoja carta (detalle + dinero + firmas).
4. Si vuelve debiendo: **Por cobrar → Abonar** una o varias veces hasta saldar.
5. **Anular** solo si no tiene cobros ni adelantos (devuelve repuestos al stock).

## Flujo 2: Compra (inventario)

1. **Compras → Nueva compra**: proveedor (opcional al contado → compra local;
   obligatorio a crédito), cuenta si es contado, líneas con costo.
   Subtotal por línea y total en vivo. Guardar: sube stock y fija el último costo.
2. Contado nace **pagada**; crédito nace **vigente** con deuda.
3. **Abonar** desde la tabla o Por pagar, parcial o total. Al saldar queda pagada.
4. **Anular** devuelve stock y revierte el dinero (si el dinero ya se gastó, avisa).
5. **Imprimir** abre la hoja carta (líneas, total, deuda, abonos).

## Flujo 3: Dinero y cuentas

- **Bancos**: ver las 4 cuentas con su dinero. Crear pide saldo inicial una sola vez;
  editar jamás toca dinero.
- **Movimientos**: entrada, salida (gasto), traspaso entre cuentas, aporte y retiro
  de socio. Toda salida pide motivo y se bloquea sin dinero.
- **Marcar** = revisado para conciliar; filtra pendientes y revisados.
- **Por cobrar / Por pagar**: quién debe y a quién debes, con buscador y filtro
  de mora (+15 días).

## Flujo 4: Facturación electrónica (cuando llegue cucu.bo)

1. Cobrar genera el comprobante interno como hoy.
2. Emitir factura desde Facturas con los datos del cliente (NIT/CI válido).
3. Si la cola falla, la factura queda pendiente y el worker la reintenta.

## Flujo 5: Cierre de mes (contador)

1. Revisar **Diario** (todo cuadra) y **Mayor** por cuenta.
2. **Balance** y **Ganancias** del mes.
3. **Equivalencias**: qué cuenta usa cada concepto (el contador lo ajusta sin código).
4. **Cierre**: bloquea el mes; lo cerrado no se registra.
5. **Reportes**: ventas, top repuestos, mecánicos, ganancia, bancos, con CSV para Excel.

## Flujo 6: Respaldos (admin)

- Automático diario + manual con **Crear respaldo ahora**.
- **Descargar**: el navegador pregunta la carpeta (USB).
- **Restaurar**: desde la lista o subiendo el `.zip` (valida que sea del sistema),
  con `RESTAURAR` + tu clave.

## Referencia rápida (pantallas secundarias)

| Pantalla | Para qué | Clave |
|---|---|---|
| Clientes/Vehículos | Ficha + vehículos; Atender crea orden precargada | Placa única |
| Productos/Categorías | Ficha, SKU automático, alerta de mínimo | Stock solo por compras |
| Proveedores | Importadoras + compra local genérica | Crédito necesita dueño |
| Usuarios/Roles | Accesos por permiso | Caja no ve contabilidad |
| Config | Empresa, SIAT, impresión | Sin código |
| Notificaciones | Stock bajo, olvidadas +7d, mora 15+d, respaldo +2d | Cada aviso abre su filtro |
