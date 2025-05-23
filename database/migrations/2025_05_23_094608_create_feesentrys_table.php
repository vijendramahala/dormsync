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
        Schema::create('feesentrys', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('hosteler_details')->nullable();
            $table->string('hosteler_id');
            $table->date('admission_date');
            $table->string('hosteler_name');
            $table->string('course_name');
            $table->string('father_name');
            $table->string('room_type');
            $table->integer('r_total_fees');
            $table->string('mess_facility');
            $table->integer('m_total_fees');
            $table->integer('discount');
            $table->integer('total_amount');
            $table->integer('EMI_recived')->default(0);
            $table->integer('EMI_total')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feesentrys');
    }
};
