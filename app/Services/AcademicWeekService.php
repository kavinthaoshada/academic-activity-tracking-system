<?php

namespace App\Services;

use App\Models\AcademicWeek;
use App\Models\Batch;
use Carbon\Carbon;

class AcademicWeekService
{
    public function generateWeeksForBatch(Batch $batch): void
    {
        $totalWeeks = $batch->programme->total_weeks;
        $startDate  = Carbon::parse($batch->start_date)->startOfWeek(Carbon::MONDAY);

        for ($weekNumber = 1; $weekNumber <= $totalWeeks; $weekNumber++) {
            $weekStart = $startDate->copy()->addWeeks($weekNumber - 1);
            $weekEnd   = $weekStart->copy()->addDays(5); // Mon to Sat

            AcademicWeek::firstOrCreate(
                ['batch_id' => $batch->id, 'week_number' => $weekNumber],
                [
                    'start_date'   => $weekStart->toDateString(),
                    'end_date'     => $weekEnd->toDateString(),
                    'working_days' => 6,
                    'week_type'    => 'full',
                    'is_locked'    => false,
                ]
            );
        }
    }

    public function updateWeekType(AcademicWeek $week, int $workingDays, ?string $notes = null): AcademicWeek
    {
        $type = match (true) {
            $workingDays >= 6 => 'full',
            $workingDays === 5 => 'reduced',
            $workingDays < 5 && $workingDays > 0 => 'holiday',
            default           => 'holiday',
        };

        $week->update([
            'working_days' => $workingDays,
            'week_type'    => $type,
            'notes'        => $notes,
        ]);

        return $week->fresh();
    }
}