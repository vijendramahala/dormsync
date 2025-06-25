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
        Schema::create('voucherentrys', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('voucher_type');
            $table->date('voucher_date');
            $table->string('voucher_no');
            $table->string('payment_mode');
            $table->decimal('payment_balance');
            $table->string('account_head');
            $table->decimal('account_balance');
            $table->decimal('debit');
            $table->decimal('credit');
            $table->string('narration');
            $table->string('paid_by');
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
        Schema::dropIfExists('voucherentrys');
    }
};
