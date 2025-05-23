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
        'room_type',
        'r_total_fees',
        'mess_facility',
        'm_total_fees',
        'discount',
        'total_amount',
        'EMI_recived',
        'EMI_total'
    ];

    protected $casts = [
        'admission_date' => 'date',
        'r_total_fees' => 'integer',
        'm_total_fees' => 'integer',
        'discount' => 'integer',
        'total_amount' => 'integer',
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
