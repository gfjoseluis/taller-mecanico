<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;
    protected $fillable = ['folio', 'supplier_id', 'bank_account_id', 'tipo_pago', 'total', 'saldo', 'estado', 'glosa'];
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function lines() { return $this->hasMany(PurchaseLine::class); }
    public function abonos() { return $this->hasMany(SupplierPayment::class); }
}
