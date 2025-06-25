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
            $table->string('admission_date');
            $table->string('hosteler_name');
            $table->string('course_name');
            $table->string('father_name');
            $table->json('fees_structure')->nullable();
            $table->string('total_amount');
            $table->integer('discount');
            $table->integer('total_remaining');
            $table->integer('EMI_recived')->default(0);
            $table->integer('EMI_total')->default(1);
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
        Schema::dropIfExists('feesentrys');
    }
};
