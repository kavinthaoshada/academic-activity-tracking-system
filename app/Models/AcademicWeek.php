<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicWeek extends Model
{
    protected $fillable = [
        'batch_id', 'week_number', 'start_date', 'end_date',
        'working_days', 'week_type', 'notes', 'is_locked', 'planned_session_overrides',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_locked'  => 'boolean',
            'planned_session_overrides' => 'array',
        ];
    }

    public function batch()          { return $this->belongsTo(Batch::class); }
    public function weeklySessions() { return $this->hasMany(WeeklySession::class); }

    public function getLabelAttribute(): string
    {
        return "Week {$this->week_number} ({$this->start_date->format('d M')} – {$this->end_date->format('d M')})";
    }
}