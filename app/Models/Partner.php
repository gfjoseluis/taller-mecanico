<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use SoftDeletes;
    protected $fillable = ['nombre', 'ci', 'telefono'];

    public function getAportesAttribute(): float
    {
        return (float) BankMovement::where('partner_id', $this->id)->where('tipo', 'aporte')->sum('monto');
    }

    public function getRetirosAttribute(): float
    {
        return (float) BankMovement::where('partner_id', $this->id)->where('tipo', 'retiro')->sum('monto');
    }
}
