<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankMovement extends Model
{
    protected $fillable = ['fecha', 'tipo', 'cuenta_id', 'cuenta_destino_id', 'partner_id', 'monto', 'motivo', 'origen_tipo', 'origen_id', 'user_id', 'revisado'];
    protected $casts = ['fecha' => 'date', 'revisado' => 'boolean'];
    public function cuenta() { return $this->belongsTo(BankAccount::class, 'cuenta_id'); }
    public function destino() { return $this->belongsTo(BankAccount::class, 'cuenta_destino_id'); }
    public function socio() { return $this->belongsTo(Partner::class, 'partner_id'); }
}
