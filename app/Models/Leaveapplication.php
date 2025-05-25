<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class Leaveapplication extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $fillable = [
        'licence_no',
        'branch_id',
        'hosteler_details',
        'hosteler_id',
        'admission_date',
        'hosteler_name',
        'course_name',
        'father_name',
        'from_date',
        'to_date',
        'accompained_by',
        'relation',
        'aadhar_no',
        'contact',
        'destination',
        'purpose_of_leave',
    ];

    // If you have date fields, Laravel can cast them automatically
    protected $casts = [
        'admission_date' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
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
