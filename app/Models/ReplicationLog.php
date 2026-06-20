<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplicationLog extends Model
{
    protected $fillable = ['branch_id', 'table_name', 'records_sent', 'records_received', 'status', 'error_message'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
