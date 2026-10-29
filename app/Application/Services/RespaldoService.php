<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/** Respaldos: SQL + archivos, descarga, borrado y restauración con doble confirmación. */
class RespaldoService
{
    public function dir(): string
    {
        Storage::disk('local')->makeDirectory('backups');
        return Storage::disk('local')->path('backups');
    }

    private function bin(string $exe): string
    {
        if ($p = env('DB_DUMP_PATH')) return rtrim($p, '/\\').DIRECTORY_SEPARATOR.$exe;
        foreach (glob('C:\\laragon\\bin\\mysql\\*\\bin\\'.$exe) ?: [] as $f) return $f;
        return $exe; // PATH
    }

    private function dsn(): array
    {
        $c = config('database.connections.mysql');
        return [$c['host'], $c['port'], $c['username'], $c['password'], $c['database']];
    }

    public function crear(): string
    {
        [$h, $port, $u, $p, $db] = $this->dsn();
        $nombre = 'respaldo-'.now()->format('Y-m-d_H-i-s').'.zip';
        $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'taller-'.uniqid();
        mkdir($tmp);
        $sql = $tmp.DIRECTORY_SEPARATOR.'base.sql';
        $cmd = sprintf('"%s" -h %s -P %s -u %s %s %s > "%s" 2>&1',
            $this->bin('mysqldump.exe'), escapeshellarg($h), escapeshellarg($port),
            escapeshellarg($u), $p === '' || $p === null ? '' : '-p'.escapeshellarg($p),
            escapeshellarg($db), $sql);
        exec($cmd, $out, $code);
        if ($code !== 0 || ! filesize($sql)) {
            throw new \RuntimeException('No se pudo exportar la base. Revisa MySQL.');
        }
        file_put_contents($tmp.'/LEEME.txt', "Respaldo Taller Alto Tonelaje\nFecha: ".now()."\nGenerado por: ".(auth()->user()->email ?? '?')."\n");
        $zip = new ZipArchive;
        $dest = $this->dir().DIRECTORY_SEPARATOR.$nombre;
        $zip->open($dest, ZipArchive::CREATE);
        $zip->addFile($sql, 'base.sql');
        $zip->addFile($tmp.'/LEEME.txt', 'LEEME.txt');
        $this->agregarCarpeta($zip, storage_path('app'), 'archivos');
        $zip->close();
        array_map('unlink', glob($tmp.'/*')); rmdir($tmp);
        return $nombre;
    }

    private function agregarCarpeta(ZipArchive $zip, string $origen, string $base): void
    {
        foreach (scandir($origen) as $f) {
            if ($f === '.' || $f === '..' || $f === 'backups') continue;
            $ruta = $origen.DIRECTORY_SEPARATOR.$f;
            if (is_dir($ruta)) $this->agregarCarpeta($zip, $ruta, $base.'/'.$f);
            else $zip->addFile($ruta, $base.'/'.$f);
        }
    }

    public function lista(): array
    {
        $out = [];
        foreach (glob($this->dir().DIRECTORY_SEPARATOR.'respaldo-*.zip') ?: [] as $f) {
            $out[] = ['nombre' => basename($f), 'tamano' => filesize($f), 'fecha' => date('Y-m-d H:i', filemtime($f))];
        }
        usort($out, fn ($a, $b) => strcmp($b['nombre'], $a['nombre']));
        return $out;
    }

    /** Fecha del último respaldo o null si nunca se hizo uno. */
    public function ultimo(): ?\Carbon\Carbon
    {
        $lista = $this->lista();
        if (empty($lista)) return null;
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i', $lista[0]['fecha']);
    }

    public function ruta(string $nombre): string
    {
        if (! preg_match('/^respaldo-[\d\-_]+\.zip$/', $nombre)) abort(404);
        $ruta = $this->dir().DIRECTORY_SEPARATOR.$nombre;
        if (! is_file($ruta)) abort(404);
        return $ruta;
    }

    /** Restaura: verifica clave, guarda respaldo previo y bloquea 1 minuto. */
    public function restaurar(string $nombre, string $clave): void
    {
        $this->verificarClave($clave);
        $this->crear(); // respaldo de seguridad previo (si algo sale mal, se vuelve atrás)
        $this->importar($this->ruta($nombre));
    }

    /** Restaura desde un archivo subido (USB): valida que sea respaldo del sistema. */
    public function restaurarSubido(\Illuminate\Http\UploadedFile $archivo, string $clave): void
    {
        $this->verificarClave($clave);
        if (strtolower($archivo->getClientOriginalExtension()) !== 'zip') {
            abort(422, 'Archivo inválido: debe ser un .zip de respaldo del sistema.');
        }
        $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sub-'.uniqid().'.zip';
        $archivo->move(dirname($tmp), basename($tmp));
        try {
            $this->crear();
            $this->importar($tmp);
        } finally {
            @unlink($tmp);
        }
    }

    private function verificarClave(string $clave): void
    {
        $u = auth()->user();
        if (! $u || ! Hash::check($clave, $u->password)) {
            abort(422, 'Contraseña incorrecta. Ej: escribe tu clave de acceso.');
        }
    }

    /** Importa un zip verificado (contiene base.sql) con mantenimiento de 1 minuto. */
    private function importar(string $rutaZip): void
    {
        $z = new ZipArchive;
        if ($z->open($rutaZip) !== true) abort(422, 'Archivo dañado.');
        if ($z->locateName('base.sql') === false) {
            $z->close();
            abort(422, 'No es respaldo del sistema: falta base.sql.');
        }
        [$h, $port, $user, $p, $db] = $this->dsn();
        $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'rest-'.uniqid();
        mkdir($tmp);
        $z->extractTo($tmp);
        $z->close();
        Artisan::call('down', ['--render' => 'errors::503', '--retry' => 60]);
        try {
            $cmd = sprintf('"%s" -h %s -P %s -u %s %s %s < "%s" 2>&1',
                $this->bin('mysql.exe'), escapeshellarg($h), escapeshellarg($port),
                escapeshellarg($user), $p === '' || $p === null ? '' : '-p'.escapeshellarg($p),
                escapeshellarg($db), $tmp.DIRECTORY_SEPARATOR.'base.sql');
            exec($cmd, $out, $code);
            if ($code !== 0) throw new \RuntimeException('No se pudo importar: '.implode("\n", $out));
        } finally {
            Artisan::call('up');
            array_map('unlink', glob($tmp.'/*')); rmdir($tmp);
        }
    }
}
