<?php

namespace App\Jobs;

use App\Models\StaffInvitation;
use App\Notifications\StaffInvitationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInvitationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public StaffInvitation $invitation) {}

    public function handle(): void
    {
        $this->invitation->notify(new StaffInvitationNotification($this->invitation));
    }
}