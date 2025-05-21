<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
    'name',
    'branch_name',
    'b_address',
    'b_city',
    'b_state',
    'licence_no',
    'contact_no', 
    'location_id'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function licence()
    {
        return $this->belongsTo(Licence::class);
    }


}
