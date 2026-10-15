<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['work_order_id', 'medio', 'bank_account_id', 'total', 'recibido', 'vuelto', 'saldo_pendiente', 'user_id'];
    public function order() { return $this->belongsTo(WorkOrder::class, 'work_order_id'); }
    public function banco() { return $this->belongsTo(BankAccount::class, 'bank_account_id'); }
}
