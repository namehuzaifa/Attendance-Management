<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date', 'check_in', 'check_out', 'status', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isLate()
    {
        if (!$this->check_in) return false;

        $graceTime = 30; // 30 minutes grace period
        $officeStart = $this->user->office_start_time;
        $checkInTime = $this->check_in;

        $lateThreshold = strtotime($officeStart) + ($graceTime * 60);

        return strtotime($checkInTime) > $lateThreshold;
    }
}
