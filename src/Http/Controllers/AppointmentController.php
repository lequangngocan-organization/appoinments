<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Anlqn\Appointments\Application\DTO\{ScheduleAppointmentCommand, RescheduleAppointmentCommand, CancelAppointmentCommand};
use Anlqn\Appointments\Application\UseCases\{ScheduleAppointmentHandler, RescheduleAppointmentHandler, CancelAppointmentHandler};

final class AppointmentController extends Controller
{
    public function store(Request $r, ScheduleAppointmentHandler $uc)
    {
        $data = $r->validate([
            'title'          => 'required|string',
            'description'    => 'nullable|string',
            'sender_email'   => 'required|email',
            'receiver_email' => 'required|email',
            'start_at'       => 'required|date_format:Y-m-d H:i:s',
            'end_at'         => 'required|date_format:Y-m-d H:i:s|after:start_at',
            'timezone'       => 'nullable|string',
        ]);

        $appt = $uc->handle(new ScheduleAppointmentCommand(
            $data['title'],
            $data['description'] ?? null,
            $data['sender_email'],
            null,
            $data['receiver_email'],
            null,
            $data['start_at'],
            $data['end_at'],
            $data['timezone'] ?? config('appointments.default_timezone')
        ));

        return response()->json(['id' => $appt->id], 201);
    }

    public function update(string $id, Request $r, RescheduleAppointmentHandler $uc)
    {
        $d = $r->validate([
            'start_at' => 'required|date_format:Y-m-d H:i:s',
            'end_at'   => 'required|date_format:Y-m-d H:i:s|after:start_at',
            'timezone' => 'nullable|string',
        ]);
        $appt = $uc->handle(new RescheduleAppointmentCommand($id, $d['start_at'], $d['end_at'], $d['timezone'] ?? null));
        return response()->json(['id' => $appt->id, 'status' => 'rescheduled']);
    }

    public function destroy(string $id, CancelAppointmentHandler $uc)
    {
        $uc->handle(new CancelAppointmentCommand($id));
        return response()->json(['status' => 'canceled']);
    }
}
