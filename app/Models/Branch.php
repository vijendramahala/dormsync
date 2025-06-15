<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Branch extends Model
{
    use HasFactory ;

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


        public function user()
    {
        return $this->hasOne(\App\Models\User::class, 'branch_id', 'id')
                    ->where('role', 'admin');
    }

    public function licence()
    {
        return $this->belongsTo(\App\Models\Licence::class, 'licence_id', 'id');
    }




}
