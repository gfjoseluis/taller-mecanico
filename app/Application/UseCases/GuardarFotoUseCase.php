<?php

namespace App\Application\UseCases;

use App\Models\Recepcion;
use App\Models\RecepcionFoto;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

/** Foto de recepción: se normaliza a 1280px JPEG para cuidar disco. */
class GuardarFotoUseCase
{
    public function ejecutar(int $ordenId, string $posicion, string $rutaTemporal): RecepcionFoto
    {
        if (! array_key_exists($posicion, RecepcionFoto::POSICIONES)) {
            throw new \DomainException('Posición inválida. Ej: usa una de las 5 vistas.');
        }
        return DB::transaction(function () use ($ordenId, $posicion, $rutaTemporal) {
            $ot = WorkOrder::findOrFail($ordenId);
            if ($ot->estado !== 'registrada') throw new \DomainException('Solo órdenes abiertas admiten fotos.');
            $recepcion = Recepcion::firstOrCreate(['work_order_id' => $ot->id], ['combustible' => 'medio', 'user_id' => auth()->id()]);
            $img = (new ImageManager(new GdDriver))->read($rutaTemporal)->scaleDown(1280);
            $ruta = "recepciones/{$ot->id}/{$posicion}.jpg";
            Storage::disk('public')->put($ruta, (string) $img->toJpeg(75));
            return RecepcionFoto::updateOrCreate(
                ['recepcion_id' => $recepcion->id, 'posicion' => $posicion],
                ['ruta' => $ruta]
            );
        });
    }
}
