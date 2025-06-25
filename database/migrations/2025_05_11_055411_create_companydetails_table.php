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
        Schema::create('companydetails', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no');
            $table->integer('branch_id');
            $table->string('business_name');
            $table->string('business_type');
            $table->string('owner_name');
            $table->string('email')->unique();
            $table->string('mobile_number');
            $table->string('landline_number')->nullable();
            $table->text('business_address');
            $table->string('pin_code');
            $table->string('std_code')->nullable();
            $table->string('state');
            $table->string('city');
            $table->string('district_or_town');
            $table->text('additional_info')->nullable();
            $table->string('information_1')->nullable();
            $table->string('information_2')->nullable();
            $table->string('information_3')->nullable();  
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
        Schema::dropIfExists('companydetails');
    }
};
