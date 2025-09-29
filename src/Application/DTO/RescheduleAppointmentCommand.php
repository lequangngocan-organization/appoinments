<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Application\DTO;

final class RescheduleAppointmentCommand
{
    public function __construct(
        public string $appointment_id,
        public string $start_at,
        public string $end_at,
        public ?string $timezone = null
    ) {}
}
