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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // Kis user ki attendance hai
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Date of attendance
            $table->date('date');

            // Check-in and Check-out
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();

            // Status fields (optional)
            $table->enum('status', ['present', 'absent', 'late', 'half-day'])->default('present');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
