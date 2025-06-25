<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class staffmaster extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $fillable = [
        'licence_no',
        'branch_id',
        'title',
        'staff_name',
        'relation_type',
        'name',
        'contact_no',
        'whatsapp_no',
        'email',
        'department',
        'designation',
        'joining_date',
        'aadhar_no',
        'permanent_address',
        'state',
        'city',
        'city_town_village',
        'address',
        'pin_code',
        'temporary_address',
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
}
