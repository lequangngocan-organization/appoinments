<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Application\UseCases;

use Anlqn\Appointments\Application\DTO\{ScheduleAppointmentCommand};
use Anlqn\Appointments\Domain\Repositories\AppointmentRepository;
use Anlqn\Appointments\Domain\Entities\{Appointment, Participant};
use Anlqn\Appointments\Domain\ValueObjects\TimeSlot;
use Anlqn\Appointments\Domain\Events\{AppointmentScheduled};

final class ScheduleAppointmentHandler
{
    public function __construct(private AppointmentRepository $repo) {}
    public function handle(ScheduleAppointmentCommand $cmd): Appointment
    {
        return \DB::transaction(function () use ($cmd) {
            $tz = $cmd->timezone ?? config('appointments.default_timezone');
            $slot = new TimeSlot(
                new \DateTimeImmutable($cmd->start_at, new \DateTimeZone($tz)),
                new \DateTimeImmutable($cmd->end_at,   new \DateTimeZone($tz)),
                $tz
            );
            $appt = new Appointment(
                id: $this->repo->nextId(),
                title: $cmd->title,
                description: $cmd->description,
                slot: $slot,
                participants: [
                    new Participant('sender', $cmd->sender_id, $cmd->sender_email),
                    new Participant('receiver', $cmd->receiver_id, $cmd->receiver_email),
                ]
            );
            $this->repo->store($appt);
            \Event::dispatch(new AppointmentScheduled($appt));
            return $appt;
        });
    }
}
