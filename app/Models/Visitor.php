<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class Visitor extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $table = 'visitors';
    protected $fillable = [
        'licence_no',
        'branch_id',
        'hosteler_details',
        'hosteler_id',
        'admission_date',
        'hosteler_name',
        'course_name',
        'father_name',
        'visiting_date',
        'visitor_name',
        'relation',
        'contact',
        'aadhar_no',
        'purpose_of_visit',
        'date_of_leave',
    ];

    //Update code
    //git add .   - to add code to repo
    //git commit -m"any name"   - to make path between repo and file
    //git push     - Transfer code to repo

    //Check code status
    //git status    - to check transfer status
    // git stash    -  to save data at another place and remove from file
    // git stash pop  - t get back data

    //Get code 
    //git pull    - to get latest data from repo

    protected $casts = [
        'admission_date' => 'date',
        'visiting_date' => 'date',
        'date_of_leave' => 'datetime',
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
