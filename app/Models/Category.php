<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    protected $fillable = ['nombre', 'prefijo', 'tipo'];
    public function products() { return $this->hasMany(Product::class); }
    public function scopeSearch($q, ?string $s) { return $s ? $q->where('nombre', 'like', "%{$s}%") : $q; }
}
