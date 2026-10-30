<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('off_days', function (Blueprint $table) {
            if (!Schema::hasColumn('off_days', 'title')) {
                $table->string('title')->after('id');
                $table->date('date')->index();
                $table->string('description')->nullable();
                $table->boolean('is_recurring')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('off_days', function (Blueprint $table) {
            $table->dropColumn(['title', 'date', 'description', 'is_recurring']);
        });
    }
};
