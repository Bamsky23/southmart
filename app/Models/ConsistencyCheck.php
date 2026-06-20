<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsistencyCheck extends Model
{
    protected $table = 'consistency_checks';

    protected $fillable = ['branch_id', 'table_name', 'branch_count', 'central_count', 'is_consistent', 'percentage'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
