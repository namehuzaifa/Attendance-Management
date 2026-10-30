<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->unique()->after('full_name');
            $table->date('joining_date')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('joining_date');
            $table->unsignedSmallInteger('annual_leave_quota')->default(20)->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employee_code', 'joining_date', 'avatar', 'annual_leave_quota']);
        });
    }
};
