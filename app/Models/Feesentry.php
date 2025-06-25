<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Feesentry extends Model
{
    use HasFactory;
    protected $table = ('feesentrys');

        protected $fillable = [
        'licence_no',
        'branch_id',
        'hosteler_details',
        'hosteler_id',
        'admission_date',
        'hosteler_name',
        'course_name',
        'father_name',
        'fees_structure',
        'total_amount',
        'discount',
        'total_remaining',
        'EMI_recived',
        'EMI_total',
        'other1',
        'other2',
        'other3',
        'other4',
        'other5',
    ];

    protected $casts = [
        'fees_structure' => 'array',
        'EMI_recived' => 'integer',
        'EMI_total' => 'integer',
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
