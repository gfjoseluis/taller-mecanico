<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proforma extends Model
{
    use SoftDeletes;
    protected $fillable = ['numero', 'client_id', 'vehicle_id', 'diagnostico', 'mano_obra', 'total_repuestos', 'descuento', 'total', 'estado', 'user_id'];
    public function client() { return $this->belongsTo(Client::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function lineas() { return $this->hasMany(ProformaLine::class); }
    public function orden() { return $this->hasOne(WorkOrder::class, 'proforma_id'); }
}
