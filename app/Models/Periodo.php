<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $fillable = ['gestion', 'mes', 'cerrado', 'user_id'];
    protected $casts = ['cerrado' => 'boolean'];

    public static function cerrado(\DateTimeInterface|string $fecha): bool
    {
        $f = $fecha instanceof \DateTimeInterface ? \Carbon\Carbon::instance($fecha) : \Carbon\Carbon::parse($fecha);
        return static::where('gestion', $f->year)->where('mes', $f->month)->where('cerrado', true)->exists();
    }
}
