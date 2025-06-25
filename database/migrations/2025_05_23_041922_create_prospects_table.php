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
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('student_name');
            $table->string('gender');
            $table->string('contact_no');
            $table->string('father_name')->nullable();
            $table->string('f_contact_no')->nullable();
            $table->string('address');
            $table->string('staff');
            $table->string('next_appointment_date');
            $table->time('time');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('prospect_status');
            $table->string('remark');
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
        Schema::dropIfExists('prospects');
    }
};
