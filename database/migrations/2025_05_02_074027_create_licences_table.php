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
        Schema::create('licences', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no')->unique();
            $table->date('license_due_date');
            $table->date('amc_due_date');
            $table->string('company_name');
            $table->string('l_address');
            $table->string('l_city');
            $table->string('l_state');
            $table->string('gst_no')->nullable();
            $table->string('owner_name');
            $table->string('contact_no');
            $table->decimal('deal_amt', 10, 2)->nullable();
            $table->decimal('receive_amt', 10, 2)->nullable();
            $table->decimal('due_amt', 10, 2)->nullable();
            $table->integer('branch_count');
            $table->json('branch_list')->nullable();
            $table->string('salesman');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licences');
    }
};
