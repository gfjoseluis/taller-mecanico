<?php

namespace App\Application\UseCases;

use App\Application\Services\SkuGenerator;
use App\Models\Category;
use App\Models\Product;

/** El SKU y el tipo nacen de la categoría (una sola fuente, sin mezclas). Editar nunca pisa costo ni stock. */
class GuardarProductoUseCase
{
    public function __construct(private SkuGenerator $sku) {}

    public function ejecutar(?int $id, array $datos): Product
    {
        $cat = Category::find($datos['category_id'] ?? null);
        if (! $cat) throw new \DomainException('Elige la categoría. Ej: Filtros.');
        $datos['tipo'] = $cat->tipo;
        if (! $id) {
            $datos['sku'] = $this->sku->generate($cat->tipo, $cat->prefijo ?? $cat->tipo);
            $datos['stock'] = 0;
        } else {
            unset($datos['precio_compra'], $datos['stock']);
        }
        return Product::updateOrCreate(['id' => $id], $datos);
    }
}
