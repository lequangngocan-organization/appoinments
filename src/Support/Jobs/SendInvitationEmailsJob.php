<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Support\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Anlqn\Appointments\Domain\Repositories\AppointmentRepository;
use Anlqn\Appointments\Application\Ports\Mailer;

final class SendInvitationEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public string $appointment_id)
    {
        $this->afterCommit();
    }

    public function handle(AppointmentRepository $repo, Mailer $mailer): void
    {
        $appt = $repo->findById($this->appointment_id);
        if (!$appt) return;
        $mailer->sendInvitation($appt);
    }
}
