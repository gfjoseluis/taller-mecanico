<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;
    protected $fillable = ['client_id', 'placa', 'marca', 'modelo', 'anio', 'chasis', 'tonelaje', 'ejes'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeSearch($q, ?string $s) { return $s ? $q->where(fn($w) => $w->where('placa', 'like', "%{$s}%")->orWhere('chasis', 'like', "%{$s}%")) : $q; }
}
