<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffDay extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'date', 'description', 'is_recurring'];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function scopeForMonth($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }
}
