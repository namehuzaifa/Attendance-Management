<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Carbon\Carbon;

/**
 * One shape for the user object across every mobile endpoint, so the app
 * never has to guess which fields a given response carries.
 */
trait PresentsUser
{
    protected function presentUser(User $user): array
    {
        $relation = $user->relation;
        $shift    = $relation?->shiftTiming;

        return [
            'id'            => $user->id,
            'full_name'     => $user->full_name,
            'employee_code' => $user->employee_code,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'role'          => $user->user_role,
            'avatar_url'    => $user->avatar_url,
            'joining_date'  => optional($user->joining_date)->toDateString(),
            'is_active'     => (bool) $user->is_active,
            'department'    => $relation?->department?->name,
            'designation'   => $relation?->designation?->name,
            'manager'       => $relation?->manager?->full_name,
            'shift'         => $shift ? [
                'name'         => $shift->name,
                'start_time'   => Carbon::parse($shift->start_time)->format('H:i'),
                'end_time'     => Carbon::parse($shift->end_time)->format('H:i'),
                'grace_period' => (int) $shift->grace_period,
                'off_days'     => $user->weeklyOffDays(),
            ] : null,
        ];
    }
}
