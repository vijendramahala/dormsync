<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Itemmaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'licence_no',
        'branch_id',
        'item_no',
        'item_name',
        'item_group',
        'manufacturer',
        'stock_qty',
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
