<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;


class Admissionform extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $fillable = [
        'licence_no',
        'branch_id',
        'admission_date',
        'image',
        'student_id',
        'student_name',
        'gender',
        'marital_status',
        'aadhar_no',
        'caste',
        'primary_contact_no',
        'whatsapp_no',
        'email',
        'college_name',
        'course',
        'date_of_birth',
        'year',
        'father_name',
        'mother_name',
        'guardian',
        'emergency_no',
        'permanent_address',
        'permanent_state',
        'permanent_city',
        'permanent_city_town',
        'permanent_pin_code',
        'temporary_address',
        'temporary_state',
        'temporary_city',
        'temporary_city_town',
        'temporary_pin_code',
    ];
   // In Admissionform.php model:

    public function licence()
    {
        return $this->belongsTo(Licence::class, 'licence_no', 'licence_no');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

}
