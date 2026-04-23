<?php

namespace App\Services;

use App\Exports\AcademicActivityExport;
use App\Models\Batch;
use App\Models\AcademicWeek;
use Maatwebsite\Excel\Facades\Excel;

class ReportService
{
    public function generateWeeklyReport(Batch $batch, AcademicWeek $week): string
    {
        $filename = "academic-activity-{$batch->id}-week-{$week->week_number}.xlsx";
        $path     = "reports/{$filename}";

        Excel::store(new AcademicActivityExport($batch, $week), $path, 'local');

        return $path;
    }
}