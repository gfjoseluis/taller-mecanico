<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use SoftDeletes;
    protected $casts = ['cerrada_at' => 'datetime'];
    protected $fillable = ['numero', 'client_id', 'vehicle_id', 'parent_id', 'proforma_id', 'estado', 'diagnostico', 'mano_obra', 'total_repuestos', 'descuento', 'total', 'tecnico_id', 'cerrada_at'];
    public function client() { return $this->belongsTo(Client::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function lineas() { return $this->hasMany(WorkOrderLine::class); }
    public function recepcion() { return $this->hasOne(Recepcion::class, 'work_order_id'); }
    public function parent() { return $this->belongsTo(WorkOrder::class, 'parent_id'); }
    public function hijas() { return $this->hasMany(WorkOrder::class, 'parent_id'); }
    public function logs() { return $this->hasMany(WorkOrderLog::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function ultimoPago() { return $this->hasOne(Payment::class)->latestOfMany(); }
    public function tecnico() { return $this->belongsTo(User::class, 'tecnico_id'); }
}
