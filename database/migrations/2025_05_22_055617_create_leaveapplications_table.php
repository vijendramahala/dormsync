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
        Schema::create('leaveapplications', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('hosteler_details')->nullable();
            $table->string('hosteler_id');
            $table->date('admission_date');
            $table->string('hosteler_name');
            $table->string('course_name');
            $table->string('father_name');
            $table->date('from_date');
            $table->date('to_date');
            $table->string('accompained_by')->nullable();
            $table->string('relation')->nullable();
            $table->string('aadhar_no', 12);
            $table->string('contact', 15);
            $table->string('destination');
            $table->string('purpose_of_leave');
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
        Schema::dropIfExists('leaveapplications');
    }
};
