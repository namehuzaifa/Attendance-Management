<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    public const TYPES = ['sick', 'casual', 'annual', 'emergency', 'unpaid'];

    protected $fillable = [
        'user_id', 'start_date', 'end_date', 'days', 'type', 'reason',
        'status', 'approved_by', 'admin_notes', 'acted_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'acted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Approved leave that covers the given date — used so a day on leave is
     * not reported as absent.
     */
    public function scopeCovering($query, $date)
    {
        return $query->approved()
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'approved'  => 'success',
            'rejected'  => 'danger',
            'cancelled' => 'secondary',
            default     => 'warning',
        };
    }
}
