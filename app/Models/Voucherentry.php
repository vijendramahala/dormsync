<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class Voucherentry extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $table = 'voucherentrys';
        protected $fillable = [
        'licence_no',
        'branch_id',
        'voucher_type',
        'voucher_date',
        'voucher_no',
        'payment_mode',
        'payment_balance',
        'account_head',
        'account_balance',
        'debit',
        'credit',
        'narration',
        'paid_by',
        'remark',
        'other1',
        'other2',
        'other3',
        'other4',
        'other5',
    ];

    protected $dates = ['voucher_date'];

    public function licence()
    {
        return $this->belongsTo(Licence::class, 'licence_no', 'licence_no');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}
