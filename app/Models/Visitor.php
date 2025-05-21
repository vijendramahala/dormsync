<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'licence_no',
        'branch_id',
        'hosteler_details',
        'hosteler_id',
        'admission_date',
        'hosteler_name',
        'course_name',
        'father_name',
        'visiting_date',
        'visitor_name',
        'relation',
        'contact',
        'aadhar_no',
        'purpose_of_visit',
        'date_of_leave',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'visiting_date' => 'date',
        'date_of_leave' => 'datetime',
    ];

     public function licence()
    {
        return $this->belongsTo(Licence::class, 'licence_no', 'licence_no');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    
}
