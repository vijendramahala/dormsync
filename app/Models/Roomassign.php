<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Roomassign extends Model
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
        'building',
        'floor',
        'room_type',
        'room_no',
        'room_beds',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'room_beds' => 'integer',
    ];

    public function licence()
    {
        return $this->belongsTo(Licence::class, 'licence_no', 'licence_no');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
    public function student()
    {
        return $this->belongsTo(Admissionform::class, 'hosteler_id', 'student_id');
    }
}
