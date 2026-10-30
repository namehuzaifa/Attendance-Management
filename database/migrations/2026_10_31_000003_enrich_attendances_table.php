<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_late')->default(false)->after('status');
            $table->boolean('is_early_out')->default(false)->after('is_late');
            $table->boolean('is_short_hour')->default(false)->after('is_early_out');
            $table->unsignedInteger('worked_minutes')->nullable()->after('is_short_hour');
            $table->unsignedInteger('late_minutes')->nullable()->after('worked_minutes');

            $table->string('check_in_ip', 45)->nullable()->after('late_minutes');
            $table->string('check_out_ip', 45)->nullable()->after('check_in_ip');
            $table->decimal('check_in_lat', 10, 7)->nullable()->after('check_out_ip');
            $table->decimal('check_in_lng', 10, 7)->nullable()->after('check_in_lat');
            $table->decimal('check_out_lat', 10, 7)->nullable()->after('check_in_lng');
            $table->decimal('check_out_lng', 10, 7)->nullable()->after('check_out_lat');
            $table->string('source', 20)->default('web')->after('check_out_lng');
            $table->foreignId('edited_by')->nullable()->after('source')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['edited_by']);
            $table->dropColumn([
                'is_late', 'is_early_out', 'is_short_hour', 'worked_minutes', 'late_minutes',
                'check_in_ip', 'check_out_ip', 'check_in_lat', 'check_in_lng',
                'check_out_lat', 'check_out_lng', 'source', 'edited_by',
            ]);
        });
    }
};
