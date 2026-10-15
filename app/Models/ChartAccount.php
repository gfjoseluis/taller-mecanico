<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartAccount extends Model
{
    use SoftDeletes;
    protected $fillable = ['codigo', 'nombre', 'tipo', 'nivel', 'padre_id', 'imputable'];
    protected $casts = ['imputable' => 'boolean'];
    public function scopeSearch($q, ?string $s) { return $s ? $q->where(fn ($w) => $w->where('codigo', 'like', "%{$s}%")->orWhere('nombre', 'like', "%{$s}%")) : $q; }
}
