<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
                $table->string('type')->default('casual');
                $table->date('start_date');
                $table->date('end_date');
                $table->unsignedSmallInteger('days')->default(1);
                $table->text('reason');
                $table->string('status')->default('pending');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('admin_notes')->nullable();
                $table->timestamp('acted_at')->nullable();

                $table->index(['user_id', 'status']);
                $table->index(['start_date', 'end_date']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'user_id', 'type', 'start_date', 'end_date', 'days',
                'reason', 'status', 'approved_by', 'admin_notes', 'acted_at',
            ]);
        });
    }
};
