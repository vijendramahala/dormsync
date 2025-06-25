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
            $table->string('admission_date');
            $table->string('student_id')->unique();
            $table->string('student_name')->unique();
            $table->string('gender');
            $table->string('marital_status');
            $table->string('aadhar_no');
            $table->string('caste');
            $table->string('primary_contact_no');
            $table->string('whatsapp_no')->nullable();
            $table->string('email')->nullable();
            $table->string('college_name')->nullable();
            $table->string('course')->nullable();
            $table->string('date_of_birth');
            $table->string('year')->nullable();
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('parent_contect');
            $table->string('guardian')->nullable();
            $table->string('emergency_no')->nullable(); 
            $table->string('permanent_address');
            $table->string('permanent_state');
            $table->string('permanent_city');
            $table->string('permanent_city_town');
            $table->string('permanent_pin_code');
            $table->string('temporary_address')->nullable();
            $table->string('temporary_state')->nullable();
            $table->string('temporary_city')->nullable();
            $table->string('temporary_city_town')->nullable();
            $table->string('temporary_pin_code')->nullable();
            $table->boolean('active_status')->default(0);
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
        Schema::dropIfExists('admissionforms');
    }
};
