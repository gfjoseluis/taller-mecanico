<?php

namespace App\Application\UseCases;

use App\Application\Services\SkuGenerator;
use App\Models\Category;
use App\Models\Product;

/** El SKU nace aquí; editar nunca pisa el costo (lo maneja la compra). */
class GuardarProductoUseCase
{
    public function __construct(private SkuGenerator $sku) {}

    public function ejecutar(?int $id, array $datos): Product
    {
        if (! $id) {
            $cat = Category::find($datos['category_id'] ?? null);
            $datos['sku'] = $this->sku->generate($datos['tipo'], $cat?->prefijo ?? $datos['tipo']);
        } else {
            unset($datos['precio_compra']);
        }
        return Product::updateOrCreate(['id' => $id], $datos);
    }
}
