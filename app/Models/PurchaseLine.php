<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseLine extends Model
{
    protected $fillable = ['purchase_id', 'product_id', 'cantidad', 'costo'];
    public function product() { return $this->belongsTo(Product::class); }
}
