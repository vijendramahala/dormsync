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
        Schema::create('roomassigns', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('hosteler_details')->nullable();
            $table->string('hosteler_id');
            $table->date('admission_date');
            $table->string('hosteler_name');
            $table->string('course_name');
            $table->string('father_name');
            $table->string('building');
            $table->string('floor');
            $table->string('room_type');
            $table->string('room_no');
            $table->Integer('room_beds');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roomassigns');
    }
};
