<?php

namespace Anlqn\Appointments\Application\Ports;

use Anlqn\Appointments\Domain\Entities\Appointment;

interface CalendarGateway
{
    public function create(Appointment $appt): ?string;

    public function update(Appointment $appt): void;
    
    public function cancel(Appointment $appt): void;
}
