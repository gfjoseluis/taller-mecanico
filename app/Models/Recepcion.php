<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recepcion extends Model
{
    protected $table = 'recepciones';
    protected $fillable = ['work_order_id', 'combustible', 'kilometraje', 'inventario', 'inventario_otros', 'inspeccion', 'observaciones', 'user_id'];
    protected $casts = ['inventario' => 'array', 'inspeccion' => 'array'];

    public const INVENTARIO = [
        'gato' => 'Gato', 'llanta_auxilio' => 'Llanta de auxilio', 'llave_ruedas' => 'Llave de ruedas',
        'extintor' => 'Extintor', 'radio' => 'Radio', 'triangulo' => 'Triángulo', 'encendedor' => 'Encendedor',
    ];

    public const INSPECCION = [
        'motor' => 'Motor', 'frenos' => 'Frenos', 'luces' => 'Luces', 'liquido_freno' => 'Líquido de freno',
        'aceite_motor' => 'Aceite de motor', 'refrigerante' => 'Refrigerante', 'bateria' => 'Batería', 'direccion' => 'Dirección',
    ];

    public const COMBUSTIBLE = [
        'vacio' => 'Vacío', 'cuarto' => '1/4', 'medio' => '1/2', 'tres_cuartos' => '3/4', 'lleno' => 'Lleno',
    ];

    public function fotos() { return $this->hasMany(RecepcionFoto::class, 'recepcion_id'); }
    public function orden() { return $this->belongsTo(WorkOrder::class, 'work_order_id'); }
}
