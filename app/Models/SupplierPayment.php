<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = ['purchase_id', 'medio', 'bank_account_id', 'monto', 'user_id'];
    public function compra() { return $this->belongsTo(Purchase::class, 'purchase_id'); }
    public function banco() { return $this->belongsTo(BankAccount::class, 'bank_account_id'); }
}
