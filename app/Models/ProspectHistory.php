<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProspectHistory extends Model
{
    protected $fillable = ['prospect_id', 'updated_by', 'old_data','prospect_status'];

    protected $casts = [
        'old_data' => 'array',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function licence()
    {
        return $this->belongsTo(Licence::class, 'licence_no', 'licence_no');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}
