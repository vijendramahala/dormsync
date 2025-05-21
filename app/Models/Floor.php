<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    protected $fillable =[
        'licence_no',
        'branch_id',
        'building_id',
        'floor'
    ];

    public function licence()
    {
        return $this->belongsTo(Licence::class, 'licence_no', 'licence_no');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id');
    }
}
