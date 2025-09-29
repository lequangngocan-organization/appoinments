<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Application\DTO;

final class ScheduleAppointmentCommand
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $sender_email,
        public int|string|null $sender_id,
        public string $receiver_email,
        public int|string|null $receiver_id,
        public string $start_at, // 'Y-m-d H:i:s'
        public string $end_at,
        public ?string $timezone = null
    ) {}
}
