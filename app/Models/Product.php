<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = ['sku', 'nombre', 'tipo', 'category_id', 'unidad', 'precio_compra', 'precio_venta', 'stock', 'stock_minimo'];
    public function category() { return $this->belongsTo(Category::class); }
    public function scopeSearch($q, ?string $s) { return $s ? $q->where(fn($w) => $w->where('nombre', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%")) : $q; }
    public function scopeBajoStock($q) { return $q->whereColumn('stock', '<=', 'stock_minimo'); }
}
