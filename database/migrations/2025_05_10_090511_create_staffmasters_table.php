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
        Schema::create('staffmasters', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no');
            $table->integer('branch_id');
            $table->string('title');
            $table->string('staff_name');
            $table->string('relation_type');
            $table->string('name');
            $table->string('contact_no');
            $table->string('whatsapp_no')->nullable();
            $table->string('email')->nullable();
            $table->string('department');
            $table->string('designation');
            $table->date('joining_date');
            $table->string('aadhar_no', 12)->nullable();
            $table->string('permanent_address');
            $table->string('state');
            $table->string('city');
            $table->string('city_town_village');
            $table->string('address');
            $table->string('pin_code', 6);
            $table->string('temporary_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staffmasters');
    }
};
