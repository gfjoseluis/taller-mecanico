<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use SoftDeletes;
    protected $fillable = ['fecha', 'glosa', 'modulo', 'referencia', 'user_id'];
    public function lines() { return $this->hasMany(JournalLine::class, 'journal_id'); }
}
