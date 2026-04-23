<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = [
        'programme_id', 'semester', 'year_range', 'division', 'start_date', 'end_date', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_active'  => 'boolean',
        ];
    }

    public function programme()     { return $this->belongsTo(Programme::class); }
    public function courses()       { return $this->hasMany(Course::class); }
    public function academicWeeks() { return $this->hasMany(AcademicWeek::class); }

    public function getFullLabelAttribute(): string
    {
        $label = "{$this->programme->code} Sem {$this->semester} ({$this->year_range})";
        if ($this->division) $label .= " Div {$this->division}";
        return $label;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}