<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licence extends Model
{
    use HasFactory;
    
    protected $table = 'licences';

    protected $fillable = [
        'licence_no',
        'license_due_date',
        'amc_due_date',
        'company_name',
        'l_address',
        'l_city',
        'l_state',
        'gst_no',
        'owner_name',
        'contact_no',
        'deal_amt',
        'receive_amt',
        'due_amt',
        'branch_count',
        'branch_list',
        'remarks',
        'salesman'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'license_due_date',
        'amc_due_date',
    ];

    public function user()
{
    return $this->hasOne(User::class, 'licence_id');
}

}
