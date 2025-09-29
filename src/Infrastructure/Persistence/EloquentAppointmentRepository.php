<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Infrastructure\Persistence;

use Anlqn\Appointments\Domain\Repositories\AppointmentRepository;
use Anlqn\Appointments\Domain\Entities\{Appointment, Participant};
use Anlqn\Appointments\Domain\ValueObjects\TimeSlot;
use Anlqn\Appointments\Domain\Enums\AppointmentStatus;
use Illuminate\Support\Str;

final class EloquentAppointmentRepository implements AppointmentRepository
{
    public function nextId(): string
    {
        return (string) Str::ulid();
    }

    public function store(Appointment $a): void
    {
        $m = EloquentAppointment::create([
            'id' => $a->id,
            'title' => $a->title,
            'description' => $a->description,
            'start_at' => $a->slot->start,
            'end_at' => $a->slot->end,
            'timezone' => $a->slot->timezone,
            'status' => $a->status->value,
            'external_event_id' => $a->external_event_id,
        ]);
        $this->syncParticipants($m, $a->participants);
    }

    public function findById(string $id): ?Appointment
    {
        $m = EloquentAppointment::with('participants')->find($id);
        if (!$m) return null;
        $slot = new TimeSlot(
            \DateTimeImmutable::createFromMutable($m->start_at),
            \DateTimeImmutable::createFromMutable($m->end_at),
            $m->timezone
        );
        $participants = $m->participants->map(fn($p) => new Participant(
            $p->role,
            $p->user_id,
            $p->email,
            $p->display_name,
            (bool)$p->is_required,
            $p->response_status
        ))->all();

        return new Appointment($m->id, $m->title, $m->description, $slot, $participants, AppointmentStatus::from($m->status), $m->external_event_id);
    }

    public function update(Appointment $a): void
    {
        $m = EloquentAppointment::findOrFail($a->id);
        $m->fill([
            'title' => $a->title,
            'description' => $a->description,
            'start_at' => $a->slot->start,
            'end_at' => $a->slot->end,
            'timezone' => $a->slot->timezone,
            'status' => $a->status->value,
            'external_event_id' => $a->external_event_id,
        ])->save();
        $this->syncParticipants($m, $a->participants);
    }

    private function syncParticipants(EloquentAppointment $m, array $participants): void
    {
        $m->participants()->delete();
        foreach ($participants as $p) {
            $m->participants()->create([
                'role' => $p->role,
                'user_id' => $p->user_id,
                'email' => $p->email,
                'display_name' => $p->display_name,
                'is_required' => $p->is_required,
                'response_status' => $p->response_status,
            ]);
        }
    }
}
