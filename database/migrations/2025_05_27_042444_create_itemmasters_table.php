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
        Schema::create('itemmasters', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('item_no');
            $table->string('item_name');
            $table->string('item_group');
            $table->string('manufacturer');
            $table->integer('stock_qty');
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
        Schema::dropIfExists('itemmasters');
    }
};
