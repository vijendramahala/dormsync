<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Prospect extends Model
{
    use HasFactory;

    protected $fillable = [
        'licence_no',
        'branch_id',
        'student_name',
        'gender',
        'contact_no',
        'father_name',
        'f_contact_no',
        'address',
        'staff',
        'next_appointment_date',
        'time',
        'city',
        'state',
        'prospect_status',
        'remark',
        'other1',
        'other2',
        'other3',
        'other4',
        'other5',
    ];

    protected $casts = [
        'time' => 'datetime:H:i:s',
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
