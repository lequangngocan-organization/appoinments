<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Application\DTO;

final class CancelAppointmentCommand
{
    public function __construct(public string $appointment_id) {}
}
