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

    /** Shape of a generated employee code, e.g. EMP-0001. */
    public const CODE_PREFIX = 'EMP-';
    public const CODE_PAD = 4;

    /** Highest number ever issued, kept so a code is never handed out twice. */
    public const CODE_COUNTER = 'last_employee_number';

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

    /**
     * What the next employee code will be, without claiming it. Safe to call
     * from a form to show the admin what they are about to get.
     *
     * Takes the higher of the rows on hand and the stored counter, so a code
     * is not recycled onto a new hire after somebody is removed.
     */
    public static function nextEmployeeCode(): string
    {
        $fromRows = (int) static::query()
            ->whereNotNull('employee_code')
            ->where('employee_code', 'like', self::CODE_PREFIX . '%')
            ->selectRaw(
                'MAX(CAST(SUBSTRING(employee_code, ?) AS UNSIGNED)) AS n',
                [strlen(self::CODE_PREFIX) + 1]
            )
            ->value('n');

        $fromCounter = (int) optional(
            Setting::where('key', self::CODE_COUNTER)->first()
        )->value;

        return self::formatEmployeeCode(max($fromRows, $fromCounter) + 1);
    }

    /**
     * Claims the next code and remembers it, so the number is spent even if
     * the account it was meant for is later deleted.
     */
    public static function issueEmployeeCode(): string
    {
        $code = static::nextEmployeeCode();

        Setting::updateOrCreate(
            ['key' => self::CODE_COUNTER],
            ['value' => (int) substr($code, strlen(self::CODE_PREFIX))]
        );

        return $code;
    }

    public static function formatEmployeeCode(int $number): string
    {
        return self::CODE_PREFIX . str_pad((string) $number, self::CODE_PAD, '0', STR_PAD_LEFT);
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
