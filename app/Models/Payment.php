<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['transaction_id', 'method', 'amount_paid', 'amount_change'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
