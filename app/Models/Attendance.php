<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date', 'check_in', 'check_out', 'status', 'notes',
        'is_late', 'is_early_out', 'is_short_hour', 'worked_minutes', 'late_minutes',
        'check_in_ip', 'check_out_ip', 'check_in_lat', 'check_in_lng',
        'check_out_lat', 'check_out_lng', 'source', 'edited_by',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'is_late' => 'boolean',
        'is_early_out' => 'boolean',
        'is_short_hour' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Human readable label built from the flags rather than the free-text
     * status column, which older records appended to repeatedly.
     */
    public function getStatusLabelAttribute(): string
    {
        $parts = [$this->is_late ? 'Late' : 'On time'];

        if ($this->is_early_out)  $parts[] = 'Early out';
        if ($this->is_short_hour) $parts[] = 'Short hour';

        return implode(' · ', $parts);
    }

    public function getWorkedHoursAttribute(): ?string
    {
        if ($this->worked_minutes === null) {
            return null;
        }

        return intdiv($this->worked_minutes, 60) . 'h ' . ($this->worked_minutes % 60) . 'm';
    }
}
