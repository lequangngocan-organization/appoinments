<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Domain\ValueObjects;

final class TimeSlot
{
    public function __construct(
        public readonly \DateTimeImmutable $start,
        public readonly \DateTimeImmutable $end,
        public readonly string $timezone
    ) {
        if ($end <= $start) {
            throw new \InvalidArgumentException('End must be after start');
        }
    }
}
