<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'employee_code',
        'email',
        'phone',
        'joining_date',
        'avatar',
        'annual_leave_quota',
        'password',
        'user_role',
        'office_start_time',
        'office_end_time',
        'email_verified_at',
        'is_active',
        'otp',
        'otp_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'office_start_time' => 'datetime:H:i',
        'office_end_time' => 'datetime:H:i',
        'joining_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = ['avatar_url'];

    public function relation()
    {
        return $this->hasOne(UserRelation::class);
    }

    public function managerOf()
    {
        return $this->hasMany(UserRelation::class, 'manager_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function offDays()
    {
        return $this->hasMany(OffDay::class);
    }

    public function isAdmin()
    {
        return $this->user_role === 'admin';
    }

    public function shift()
    {
        return optional($this->relation)->shiftTiming;
    }

    /**
     * Weekly rest days for this user, falling back to the weekend when the
     * shift has none configured.
     */
    public function weeklyOffDays(): array
    {
        $shift = $this->shift();

        return ($shift && is_array($shift->off_days) && $shift->off_days)
            ? $shift->off_days
            : ['Saturday', 'Sunday'];
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : asset('assets/images/avatar.png');
    }
}
