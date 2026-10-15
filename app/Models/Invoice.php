<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;
    protected $fillable = ['cuf', 'numero', 'origen_tipo', 'origen_id', 'client_id', 'total', 'estado', 'qr', 'raw'];
    protected $casts = ['raw' => 'array'];
}
