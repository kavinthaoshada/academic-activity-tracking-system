<?php

namespace App\Jobs;

use App\Models\Batch;
use App\Models\AcademicWeek;
use App\Models\User;
use App\Services\ReportService;
use App\Notifications\ReportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateWeeklyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public Batch        $batch,
        public AcademicWeek $week,
        public User         $requestedBy,
    ) {}

    public function handle(ReportService $reportService): void
    {
        $path = $reportService->generateWeeklyReport($this->batch, $this->week);
        $this->requestedBy->notify(new ReportReadyNotification($path, $this->batch, $this->week));
    }
}