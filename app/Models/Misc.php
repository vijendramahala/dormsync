<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Misc extends Model
{
    use HasFactory;

    protected $fillable = [
        'licence_no',
        'misc_id',
        'name',
        'other1',
        'other2',
        'other3',
        'other4',
        'other5',
    ];

    public function licence()
    {
        return $this->belongsTo(Licence::class, 'licence_no');
    }
}
