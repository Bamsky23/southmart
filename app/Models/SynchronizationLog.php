<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SynchronizationLog extends Model
{
    protected $fillable = ['branch_id', 'action', 'records_synced', 'status'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
