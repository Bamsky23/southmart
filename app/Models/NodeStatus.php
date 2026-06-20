<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NodeStatus extends Model
{
    protected $table = 'node_status';

    protected $fillable = ['branch_id', 'node_status', 'db_status', 'last_sync'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
