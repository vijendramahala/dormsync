<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ledgermaster', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no');
            $table->unsignedBigInteger('branch_id');
            $table->string('student_id')->nullable();
            $table->string('title');
            $table->string('ledger_name')->unique();
            $table->string('relation_type');
            $table->string('name');
            $table->string('contact_no', 15); // string is fine for phone numbers
            $table->string('whatsapp_no', 15)->nullable();
            $table->string('email')->nullable();
            $table->string('ledger_group');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->string('opening_type');
            $table->string('gst_no')->nullable();
            $table->string('aadhar_no', 20)->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('state');
            $table->string('city');
            $table->string('city_town_village')->nullable();
            $table->string('pin_code', 10)->nullable();
            $table->string('temporary_address')->nullable();    
            $table->string('t_state')->nullable();   
            $table->string('t_city')->nullable();
            $table->string('t_city_town_village')->nullable();
            $table->string('t_pin_code', 10)->nullable();
            $table->string('other1')->nullable();
            $table->string('other2')->nullable();
            $table->string('other3')->nullable();
            $table->string('other4')->nullable();
            $table->string('other5')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgermaster');
    }
};
