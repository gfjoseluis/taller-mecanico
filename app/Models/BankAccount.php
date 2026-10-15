<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use SoftDeletes;
    protected $fillable = ['nombre', 'banco', 'numero', 'moneda', 'saldo', 'saldo_inicial', 'corte_at', 'activa'];
    protected $casts = ['activa' => 'boolean', 'corte_at' => 'datetime'];
}
