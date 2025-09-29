<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Application\UseCases;

use Anlqn\Appointments\Application\DTO\{RescheduleAppointmentCommand};
use Anlqn\Appointments\Domain\Repositories\AppointmentRepository;
use Anlqn\Appointments\Domain\Entities\{Appointment};
use Anlqn\Appointments\Domain\ValueObjects\TimeSlot;
use Anlqn\Appointments\Domain\Events\{AppointmentRescheduled};

final class RescheduleAppointmentHandler
{
    public function __construct(private AppointmentRepository $repo) {}
    public function handle(RescheduleAppointmentCommand $cmd): Appointment
    {
        return \DB::transaction(function () use ($cmd) {
            $appt = $this->repo->findById($cmd->appointment_id) ?? throw new \RuntimeException('Appointment not found');
            $tz = $cmd->timezone ?? config('appointments.default_timezone');
            $appt->reschedule(new TimeSlot(
                new \DateTimeImmutable($cmd->start_at, new \DateTimeZone($tz)),
                new \DateTimeImmutable($cmd->end_at,   new \DateTimeZone($tz)),
                $tz
            ));
            $this->repo->update($appt);
            \Event::dispatch(new AppointmentRescheduled($appt));
            return $appt;
        });
    }
}
