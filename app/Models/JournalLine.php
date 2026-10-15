<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    protected $fillable = ['journal_id', 'account_id', 'debe', 'haber'];
    public function account() { return $this->belongsTo(ChartAccount::class, 'account_id'); }
}
