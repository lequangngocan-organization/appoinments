<?php

namespace Anlqn\Appointments\Application\Ports;

use Anlqn\Appointments\Domain\Entities\Appointment;

interface Mailer
{
    public function sendInvitation(Appointment $appt): void;

    public function sendUpdated(Appointment $appt): void;
    
    public function sendCanceled(Appointment $appt): void;
}
