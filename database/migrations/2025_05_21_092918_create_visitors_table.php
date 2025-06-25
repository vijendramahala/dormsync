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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no');
            $table->unsignedBigInteger('branch_id');
            $table->string('hosteler_details');
            $table->string('hosteler_id');
            $table->date('admission_date');
            $table->string('hosteler_name');
            $table->string('course_name');
            $table->string('father_name');
            $table->date('visiting_date');
            $table->string('visitor_name');
            $table->string('relation');
            $table->string('contact');
            $table->string('aadhar_no');
            $table->string('purpose_of_visit');
            $table->dateTime('date_of_leave');
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
        Schema::dropIfExists('visitors');
    }
};
