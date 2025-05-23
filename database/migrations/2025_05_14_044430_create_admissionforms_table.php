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
        Schema::create('admissionforms', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no');
            $table->integer('branch_id');
            $table->integer('ledger_id');
            $table->date('admission_date');
            $table->string('image');
            $table->string('student_id');
            $table->string('student_name');
            $table->string('gender');
            $table->string('marital_status');
            $table->string('aadhar_no');
            $table->string('caste');
            $table->string('primary_contact_no');
            $table->string('whatsapp_no');
            $table->string('email');
            $table->string('college_name');
            $table->string('course');
            $table->date('date_of_birth');
            $table->string('year');
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('guardian');
            $table->string('emergency_no'); 
            $table->string('permanent_address');
            $table->string('permanent_state');
            $table->string('permanent_city');
            $table->string('permanent_city_town');
            $table->string('permanent_pin_code');
            $table->string('temporary_address');
            $table->string('temporary_state');
            $table->string('temporary_city');
            $table->string('temporary_city_town');
            $table->string('temporary_pin_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissionforms');
    }
};
