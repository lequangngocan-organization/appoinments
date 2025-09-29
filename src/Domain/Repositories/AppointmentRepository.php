<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Domain\Repositories;

use Anlqn\Appointments\Domain\Entities\Appointment;

interface AppointmentRepository
{
    public function nextId(): string;

    public function store(Appointment $appt): void;

    public function findById(string $id): ?Appointment;

    public function update(Appointment $appt): void;
}
