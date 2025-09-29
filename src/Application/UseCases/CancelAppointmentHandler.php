<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Application\UseCases;

use Anlqn\Appointments\Application\DTO\{CancelAppointmentCommand};
use Anlqn\Appointments\Domain\Repositories\AppointmentRepository;
use Anlqn\Appointments\Domain\Events\{AppointmentCanceled};

final class CancelAppointmentHandler
{
    public function __construct(private AppointmentRepository $repo) {}

    public function handle(CancelAppointmentCommand $cmd): void
    {
        \DB::transaction(function () use ($cmd) {
            $appt = $this->repo->findById($cmd->appointment_id) ?? throw new \RuntimeException('Appointment not found');
            $appt->cancel();
            $this->repo->update($appt);
            \Event::dispatch(new AppointmentCanceled($appt));
        });
    }
}
