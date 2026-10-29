<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderLine extends Model
{
    protected $fillable = ['work_order_id', 'product_id', 'tipo', 'descripcion', 'cantidad', 'precio'];

    protected static function booted(): void
    {
        // El tipo sale del producto elegido; una línea sin producto es un servicio.
        static::creating(function (self $l) {
            if (empty($l->tipo)) {
                $l->tipo = $l->product_id ? (Product::where('id', $l->product_id)->value('tipo') ?? 'repuesto') : 'servicio';
            }
        });
    }

    public function product() { return $this->belongsTo(Product::class); }

    /** Nombre a mostrar: descripción libre (servicio) o nombre del producto. */
    public function getEtiquetaAttribute(): string
    {
        return $this->descripcion ?: ($this->product->nombre ?? '—');
    }

    /** Referencia corta: SKU del producto (los servicios no tienen). */
    public function getReferenciaAttribute(): string
    {
        return $this->product->sku ?? '';
    }
}
