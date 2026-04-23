<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'batch_id', 'name', 'code', 'type', 'total_hours', 'credit_hours', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function batch()           { return $this->belongsTo(Batch::class); }
    public function assignments()     { return $this->hasMany(CourseAssignment::class); }
    public function faculty()         { return $this->hasManyThrough(User::class, CourseAssignment::class, 'course_id', 'id', 'id', 'user_id'); }
    public function weeklySessions()  { return $this->hasMany(WeeklySession::class); }

    // Weekly target based on semester total_weeks (the 15-week multiplier)
    public function getWeeklyTargetAttribute(): float
    {
        $totalWeeks = $this->batch->programme->total_weeks;
        return $this->total_hours / $totalWeeks;
    }
}