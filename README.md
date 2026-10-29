# Taller Alto Tonelaje — Sistema de gestión (Laravel 10 + MySQL, en local)

Órdenes de servicio (un vehículo por orden), inventario con SKU automático, compras,
dinero (caja y bancos), contabilidad automática, reportes, respaldos y facturación
electrónica SIAT (vía proveedor externo).

## Requisitos

- Laragon (Apache + MySQL + PHP 8.1+) o servidor equivalente con PHP 8.1, MySQL y Composer.
- Node 18+ solo para compilar el diseño una vez (`npm run css`).
- Red local para las terminales (ver `README_LAN.md`).

## Instalación nueva (PC del cliente)

```bat
cd C:\laragon\www\taller-mecanico
copy .env.example .env
php artisan key:generate
```

Editar `.env`:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=http://IP-DEL-SERVIDOR
DB_DATABASE=taller_mecanico
DB_USERNAME=root
DB_PASSWORD=
QUEUE_CONNECTION=database
```

Luego:

```bat
composer install --no-dev
php artisan migrate --force
php artisan db:seed
php artisan storage:link
php artisan config:cache & php artisan route:cache & php artisan view:cache
```

Entrar con **admin@taller.test / admin123** y cambiar la clave. Crear las 4 cuentas,
el plan de cuentas ya viene sembrado (incluye 2.2 Adelantos de clientes).

Servicios permanentes de Windows (Programador de tareas):

- Cola: `php artisan queue:work --tries=5` (facturación electrónica).
- Respaldo 02:00 diario + `php artisan schedule:run` cada minuto.

## Desarrollo y pruebas (esta máquina)

```bat
composer install
copy .env.example .env
php artisan migrate --seed
```

Base separada de pruebas (no ensucia tu trabajo): crear `taller_mecanico_prueba`
y copiar `.env` a `.env.testing` cambiando `DB_DATABASE` y `APP_ENV=testing`.

```bat
php artisan test            # suite completa (usa la base de prueba)
npm run css                 # solo si cambias vistas o el tema
```

## Uso por rol

- **Caja**: Servicio → Órdenes (crear, Cobrar con Total/Ya dio/Falta, Queda debiendo),
  Clientes y Vehículos, Por cobrar (abonos), Movimientos.
- **Mecánico**: ver órdenes y stock (sin dinero ni costos).
- **Contador**: Contabilidad (diario, mayor, balance, resultados, asientos manuales,
  equivalencias, cierre de mes), Reportes, Facturas.
- **Admin**: todo + Usuarios, Roles, Config, Respaldos.

Reglas de oro: orden cerrada no se edita (se abre una nueva); el dinero nunca deja
cuentas en negativo; el stock solo entra por compras; editar ficha jamás toca dinero
ni stock; anular revierte en vez de borrar.

## Respaldos

- Automático diario 02:00 en el servidor + botón manual en Respaldos.
- **Descargar**: el navegador pregunta la carpeta (USB). Activa "preguntar dónde
  guardar" en el navegador.
- **Restaurar**: desde la lista o subiendo el `.zip` (valida que sea del sistema),
  escribiendo `RESTAURAR` + tu clave de acceso. Guarda copia previa y pone el
  sistema en mantenimiento 1 minuto.

## Fallas típicas

| Síntoma | Causa probable | Solución |
|---|---|---|
| Página en blanco / 500 | Caché vieja tras actualizar | `php artisan config:clear route:clear view:clear` |
| Se ve sin diseño | Falta compilar | `npm run css` |
| Factura queda en cola | Worker apagado | Iniciar `queue:work` |
| Respaldo 02:00 no sale | Programador apagado | Revisar tarea `schedule:run` |
| "Sin dinero suficiente" | Cuenta sin dinero | Cobrar a caja o hacer traspaso |
| CSV abre raro en Excel | — | Ya sale con `;` y tildes; abrir directo |
| Tests ensucian datos | Base real en uso | Usar `.env.testing` + `taller_mecanico_prueba` |

Más detalle de red local: ver `README_LAN.md`.
