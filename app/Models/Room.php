<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable =[
        'licence_no',
        'branch_id',
        'building_id',
        'floor_id',
        'room_no',
        'room_type',
        'room_beds',
        'current_occupants',
        'occupancy_status',
        'other1',
        'other2',
        'other3',
        'other4',
        'other5',
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
    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id', 'id');
    }
    public function admission()
    {
        return $this->belongsTo(Admissionform::class, 'hosteler_id', 'student_id');
    }


}
