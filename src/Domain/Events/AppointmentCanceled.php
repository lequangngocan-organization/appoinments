<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Domain\Events;

use Anlqn\Appointments\Domain\Entities\Appointment;

final class AppointmentCanceled
{
    public function __construct(public readonly Appointment $appt) {}
}
