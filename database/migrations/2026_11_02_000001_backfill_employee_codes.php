<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Employee codes were optional and nobody filled them in, so every
     * existing account has none. Issue them in joining order so the oldest
     * staff get the lowest numbers.
     */
    public function up(): void
    {
        $next = (int) DB::table('users')
            ->whereNotNull('employee_code')
            ->where('employee_code', 'like', User::CODE_PREFIX . '%')
            ->selectRaw(
                'MAX(CAST(SUBSTRING(employee_code, ?) AS UNSIGNED)) AS n',
                [strlen(User::CODE_PREFIX) + 1]
            )
            ->value('n');

        DB::table('users')
            ->whereNull('employee_code')
            ->orderBy('id')
            ->select('id')
            ->get()
            ->each(function ($row) use (&$next) {
                $next++;

                DB::table('users')->where('id', $row->id)->update([
                    'employee_code' => User::CODE_PREFIX
                        . str_pad((string) $next, User::CODE_PAD, '0', STR_PAD_LEFT),
                ]);
            });

        // Spend those numbers, so none is handed out again later.
        if ($next > 0) {
            DB::table('settings')->updateOrInsert(
                ['key' => User::CODE_COUNTER],
                ['value' => $next, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        // Leaving the codes in place: clearing them would strip identifiers
        // that may since have been written down or printed.
    }
};
