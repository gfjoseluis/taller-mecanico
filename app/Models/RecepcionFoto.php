<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecepcionFoto extends Model
{
    protected $table = 'recepcion_fotos';
    protected $fillable = ['recepcion_id', 'posicion', 'ruta'];

    public const POSICIONES = [
        'arriba' => 'Arriba', 'adelante' => 'Adelante', 'atras' => 'Atrás',
        'derecha' => 'Derecha', 'izquierda' => 'Izquierda',
    ];

    public const GUIAS = [
        'arriba' => 'arriba.PNG', 'adelante' => 'adelante.PNG', 'atras' => 'atras.PNG',
        'derecha' => 'derecha.PNG', 'izquierda' => 'izquierda.PNG',
    ];

    public function recepcion() { return $this->belongsTo(Recepcion::class); }
}
