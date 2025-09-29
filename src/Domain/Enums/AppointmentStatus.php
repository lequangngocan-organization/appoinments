<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Domain\Enums;

enum AppointmentStatus: string
{
    case SCHEDULED = 'scheduled';
    case RESCHEDULED = 'rescheduled';
    case CANCELED = 'canceled';
}
