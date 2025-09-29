<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Support\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Anlqn\Appointments\Application\Ports\CalendarGateway;
use Anlqn\Appointments\Domain\Repositories\AppointmentRepository;

final class CancelCalendarEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $appointmentId) {}

    public function handle(AppointmentRepository $repo, CalendarGateway $calendar): void
    {
        $appt = $repo->findById($this->appointmentId);
        if (!$appt) {
            return;
        }

        try {
            $calendar->cancel($appt); // GoogleCalendarGateway call API và sendUpdates by config
        } catch (\Throwable $e) {
            \Log::error('Google cancel failed: ' . $e->getMessage(), ['ex' => $e]);
            throw $e;
        }
    }
}
