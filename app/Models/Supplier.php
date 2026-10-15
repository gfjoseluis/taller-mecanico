<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $fillable = ['nombre', 'nit', 'telefono', 'direccion', 'saldo'];
    public function scopeSearch($q, ?string $s) { return $s ? $q->where('nombre', 'like', "%{$s}%")->orWhere('nit', 'like', "%{$s}%") : $q; }
}
