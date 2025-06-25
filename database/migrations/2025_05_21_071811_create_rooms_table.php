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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('licence_no');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('floor_id');
            $table->string('room_no');
            $table->string('room_type');
            $table->unsignedInteger('room_beds');
            $table->integer('current_occupants')->default(0);
            $table->string('occupancy_status');
            $table->json('hosteler_id')->nullable();
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
        Schema::dropIfExists('rooms');
    }
};
