<?php

namespace App\Services;

use App\Models\AcademicWeek;
use App\Models\Course;
use App\Models\WeeklySession;

class SessionPlanningService
{
    public function calculatePlannedSessions(Course $course, AcademicWeek $week, bool $ignoreOverride = false): int
    {
        if (!$ignoreOverride && is_array($week->planned_session_overrides) && isset($week->planned_session_overrides[$course->id])) {
            return (int) $week->planned_session_overrides[$course->id];
        }

        $weeklyTarget = $course->total_hours / $course->batch->programme->total_weeks;

        return match ($week->week_type) {
            'full'    => (int) round($weeklyTarget),
            'reduced' => 1,
            'holiday' => 0,
            'custom'  => (int) round($weeklyTarget * ($week->working_days / 6)),
            default   => (int) round($weeklyTarget),
        };
    }

    public function calculateCumulativeTarget(Course $course, int $upToWeekNumber): int
    {
        $weeks = $course->batch->academicWeeks()
            ->where('week_number', '<=', $upToWeekNumber)
            ->orderBy('week_number')
            ->get();

        return $weeks->sum(fn ($week) => $this->calculatePlannedSessions($course, $week));
    }

    public function saveSession(array $data): WeeklySession
    {
        $course = Course::findOrFail($data['course_id']);
        $week   = AcademicWeek::findOrFail($data['academic_week_id']);

        $previousSessions = WeeklySession::where('course_id', $course->id)
            ->whereHas('academicWeek', fn ($q) => $q->where('week_number', '<', $week->week_number))
            ->with('academicWeek')
            ->get();

        $cumulativePlanned = $previousSessions->sum('planned_sessions') + ($data['planned_sessions'] ?? 0);
        $cumulativeActual  = $previousSessions->sum('actual_sessions')  + ($data['actual_sessions']  ?? 0);
        $cumulativeTarget  = $this->calculateCumulativeTarget($course, $week->week_number);

        return WeeklySession::updateOrCreate(
            ['course_id' => $course->id, 'academic_week_id' => $week->id],
            [
                'user_id'              => $data['user_id'],
                'planned_sessions'     => $data['planned_sessions'] ?? 0,
                'actual_sessions'      => $data['actual_sessions']  ?? 0,
                'cumulative_target'    => $cumulativeTarget,
                'cumulative_planned'   => $cumulativePlanned,
                'cumulative_actual'    => $cumulativeActual,
                'remarks'              => $data['remarks'] ?? null,
            ]
        );
    }

    public function syncAndRecalculate(AcademicWeek $editedWeek): void
    {
        $sessionsToSync = WeeklySession::where('academic_week_id', $editedWeek->id)->with('course')->get();

        foreach ($sessionsToSync as $session) {
            $newPlanned = $this->calculatePlannedSessions($session->course, $editedWeek);
            
            $this->saveSession([
                'course_id'        => $session->course_id,
                'academic_week_id' => $session->academic_week_id,
                'user_id'          => $session->user_id,
                'planned_sessions' => $newPlanned, 
                'actual_sessions'  => $session->actual_sessions,
                'remarks'          => $session->remarks,
            ]);
        }

        $futureWeeks = $editedWeek->batch->academicWeeks()
            ->where('week_number', '>', $editedWeek->week_number)
            ->orderBy('week_number')
            ->get();

        foreach ($futureWeeks as $futureWeek) {
            $futureSessions = WeeklySession::where('academic_week_id', $futureWeek->id)->get();
            
            foreach ($futureSessions as $fs) {
                $this->saveSession([
                    'course_id'        => $fs->course_id,
                    'academic_week_id' => $fs->academic_week_id,
                    'user_id'          => $fs->user_id,
                    'planned_sessions' => $fs->planned_sessions,
                    'actual_sessions'  => $fs->actual_sessions,
                    'remarks'          => $fs->remarks,
                ]);
            }
        }
    }
}