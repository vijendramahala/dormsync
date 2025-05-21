<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Companydetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'licence_no',
        'branch_id',
        'business_name',
        'business_type',
        'owner_name',
        'email',
        'mobile_number',
        'landline_number',
        'business_address',
        'pin_code',
        'std_code',
        'state',
        'city',
        'district_or_town',
        'additional_info',
        'information_1',
        'information_2',
        'information_3',
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
