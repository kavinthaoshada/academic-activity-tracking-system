<?php

namespace App\Notifications;

use App\Models\AcademicWeek;
use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string       $path,
        public Batch        $batch,
        public AcademicWeek $week,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Report Ready — {$this->batch->full_label} · Week {$this->week->week_number}")
            ->view('emails.report-ready', [
                'notifiable' => $notifiable,
                'path'       => $this->path,
                'batch'      => $this->batch,
                'week'       => $this->week,
            ]);
    }

    public function backoff(): array
    {
        return [30, 60, 120];
    }
}