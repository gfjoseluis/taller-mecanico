<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;
    protected $fillable = ['nombre', 'nit_ci', 'telefono', 'email', 'direccion', 'saldo'];
    public function vehicles() { return $this->hasMany(Vehicle::class); }
    public function scopeSearch($q, ?string $s) { return $s ? $q->where(fn($w) => $w->where('nombre', 'like', "%{$s}%")->orWhere('nit_ci', 'like', "%{$s}%")) : $q; }
}
