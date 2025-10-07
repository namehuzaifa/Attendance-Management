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
        Schema::create('shift_timings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Morning, Evening etc.
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('grace_period')->default(0); // minutes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_timings');
    }
};
