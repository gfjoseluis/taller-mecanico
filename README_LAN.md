# Operación on-premise sin `php artisan serve` (Laragon)

1. Laragon > Apache > activar. El proyecto ya está en `C:\laragon\www\taller-mecanico`.
2. Laragon crea auto VirtualHost `http://taller-mecanico.test` apuntando a `/public`.
   - Si no existe: Laragon > WWW > Virtual Hosts > Add `taller-mecanico.test`.
3. Terminales LAN: usar `http://IP-DEL-SERVIDOR` (ej `http://192.168.1.50`).
   - Fijar IP estática al servidor. Abrir puerto 80 en Firewall Windows.
   - Opcional: agregar en cada terminal `hosts`: `192.168.1.50 taller-mecanico.test`.
4. Cola SIAT/offline (1 worker permanente, reemplaza serve):
   - Task Scheduler > `php C:\laragon\www\taller-mecanico\artisan queue:work --tries=5`
   - O con NSSM como servicio Windows.
5. Producción LAN: `APP_ENV=production`, `APP_DEBUG=false`, `npm run build`, `php artisan config:cache route:cache view:cache`.
6. Backups: tarea diaria `mysqldump taller_mecanico` + `storage/`.
