<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $fillable = ['transaction_id', 'receipt_code', 'printed_at'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
