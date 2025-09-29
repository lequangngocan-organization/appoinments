<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Domain\Entities;

use Anlqn\Appointments\Domain\ValueObjects\TimeSlot;
use Anlqn\Appointments\Domain\Enums\AppointmentStatus;

final class Appointment
{
    public function __construct(
        public readonly string $id,
        public string $title,
        public ?string $description,
        public TimeSlot $slot,
        /** @var Participant[] */
        public array $participants,
        public AppointmentStatus $status = AppointmentStatus::SCHEDULED,
        public ?string $external_event_id = null
    ) {}

    public function reschedule(TimeSlot $newSlot): void
    {
        $this->slot = $newSlot;
        $this->status = AppointmentStatus::RESCHEDULED;
    }

    public function cancel(): void
    {
        $this->status = AppointmentStatus::CANCELED;
    }

    public function sender(): ?Participant
    {
        foreach ($this->participants as $p) {
            if ($p->role === 'sender') return $p;
        }
        return null;
    }
}
