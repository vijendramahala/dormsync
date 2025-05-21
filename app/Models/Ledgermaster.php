<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class Ledgermaster extends Model implements HasMedia
{

    use HasFactory,InteractsWithMedia;

    protected $table = 'ledgermaster';

        protected $fillable = [
        'licence_no',
        'branch_id',
        'title',
        'ledger_name',
        'relation_type',
        'name',
        'contact_no',
        'whatsapp_no',
        'email',
        'ledger_group',
        'opening_balance',
        'opening_type',
        'gst_no',
        'aadhar_no',
        'permanent_address',
        'state',
        'city',
        'city_town_village',
        'address',
        'pin_code',
        'temporary_address',
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
