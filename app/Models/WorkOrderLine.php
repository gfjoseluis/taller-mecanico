<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderLine extends Model
{
    protected $fillable = ['work_order_id', 'product_id', 'cantidad', 'precio'];
    public function product() { return $this->belongsTo(Product::class); }
}
