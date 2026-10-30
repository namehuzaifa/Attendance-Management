<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A user may only have one profile row — older code created a new
        // relation on every save, so collapse the duplicates to the newest.
        DB::statement('
            DELETE r FROM user_relations r
            INNER JOIN user_relations keep
                ON keep.user_id = r.user_id AND keep.id > r.id
        ');

        // Same story for attendance: one row per user per day.
        DB::statement('
            DELETE a FROM attendances a
            INNER JOIN attendances keep
                ON keep.user_id = a.user_id AND keep.date = a.date AND keep.id > a.id
        ');

        Schema::table('user_relations', function (Blueprint $table) {
            $table->unique('user_id', 'user_relations_user_id_unique');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['user_id', 'date'], 'attendances_user_id_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_relations', function (Blueprint $table) {
            $table->dropUnique('user_relations_user_id_unique');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_user_id_date_unique');
        });
    }
};
