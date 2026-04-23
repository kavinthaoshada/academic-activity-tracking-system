<?php

namespace App\Notifications;

use App\Models\AcademicWeek;
use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklySessionReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AcademicWeek $week,
        public Batch        $batch,
        public int          $pendingCourses,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reminder: Log Sessions for {$this->week->label} — {$this->batch->full_label}")
            ->view('emails.session-reminder', [
                'notifiable'     => $notifiable,
                'week'           => $this->week,
                'batch'          => $this->batch,
                'pendingCourses' => $this->pendingCourses,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'           => "Sessions pending: {$this->week->label}",
            'message'         => "{$this->pendingCourses} course(s) need session data in {$this->batch->full_label}",
            'batch_id'        => $this->batch->id,
            'week_id'         => $this->week->id,
            'pending_courses' => $this->pendingCourses,
            'action_url'      => route('sessions.create', [
                'batch_id' => $this->batch->id,
                'week_id'  => $this->week->id,
            ]),
        ];
    }

    public function backoff(): array
    {
        return [30, 60, 120];
    }
}