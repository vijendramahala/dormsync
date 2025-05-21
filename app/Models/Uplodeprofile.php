<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class Uplodeprofile extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $fillable = [
        'licence_no',
        'branch_id'
    ];

    public function licence()
    {
        return $this->belongsTo(Licence::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
