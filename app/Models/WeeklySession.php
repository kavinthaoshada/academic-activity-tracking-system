<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklySession extends Model
{
    protected $fillable = [
        'course_id', 'academic_week_id', 'user_id',
        'planned_sessions', 'actual_sessions',
        'cumulative_target', 'cumulative_planned', 'cumulative_actual',
        'remarks',
    ];

    public function course()       { return $this->belongsTo(Course::class); }
    public function academicWeek() { return $this->belongsTo(AcademicWeek::class); }
    public function user()         { return $this->belongsTo(User::class); }
}