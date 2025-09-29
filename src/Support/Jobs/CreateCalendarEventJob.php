<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Support\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Anlqn\Appointments\Domain\Repositories\AppointmentRepository;
use Anlqn\Appointments\Application\Ports\CalendarGateway;

final class CreateCalendarEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public string $appointment_id)
    {
        $this->afterCommit();
    }

    public function handle(AppointmentRepository $repo, CalendarGateway $cal): void
    {
        $appt = $repo->findById($this->appointment_id);
        if (!$appt) return;
        $eventId = $cal->create($appt);
        if ($eventId) {
            $appt->external_event_id = $eventId;
            $repo->update($appt);
        }
        SendInvitationEmailsJob::dispatch($appt->id)->afterCommit();
    }
}
