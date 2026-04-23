<?php

namespace App\Console\Commands;

use App\Models\AcademicWeek;
use App\Models\Batch;
use App\Models\CourseAssignment;
use App\Models\User;
use App\Notifications\WeeklySessionReminderNotification;
use Illuminate\Console\Command;

class SendWeeklySessionReminders extends Command
{
    protected $signature   = 'sessions:send-reminders';
    protected $description = 'Send weekly session reminder emails to staff with pending entries';

    public function handle(): void
    {
        $this->info('Sending weekly session reminders...');

        $batches = Batch::where('is_active', true)->with('programme')->get();

        foreach ($batches as $batch) {
            $currentWeek = AcademicWeek::where('batch_id', $batch->id)
                ->where('is_locked', false)
                ->where('start_date', '<=', now())
                ->orderByDesc('week_number')
                ->first();

            if (!$currentWeek) continue;

            $assignments = CourseAssignment::whereHas('course', fn ($q) => $q->where('batch_id', $batch->id))
                ->with('user', 'course')
                ->get()
                ->groupBy('user_id');

            foreach ($assignments as $userId => $userAssignments) {
                $user = $userAssignments->first()->user;
                if (!$user || !$user->is_active) continue;

                $courseIds = $userAssignments->pluck('course_id');
                $loggedIds = \App\Models\WeeklySession::where('academic_week_id', $currentWeek->id)
                    ->whereIn('course_id', $courseIds)
                    ->pluck('course_id');

                $pendingCount = $courseIds->diff($loggedIds)->count();

                if ($pendingCount > 0) {
                    $user->notify(new WeeklySessionReminderNotification($currentWeek, $batch, $pendingCount));
                    $this->line("  Notified {$user->name} — {$pendingCount} pending in {$batch->full_label}");
                }
            }
        }

        $this->info('Done.');
    }
}