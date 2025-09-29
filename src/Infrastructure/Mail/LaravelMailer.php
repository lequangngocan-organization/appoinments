<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Infrastructure\Mail;

use Anlqn\Appointments\Application\Ports\Mailer;
use Anlqn\Appointments\Domain\Entities\Appointment;
use Carbon\CarbonImmutable;

use function Anlqn\Appointments\calendar_add_template_url;
use function Anlqn\Appointments\calendar_open_url;

final class LaravelMailer implements Mailer
{
    public function sendInvitation(Appointment $a): void
    {
        $this->send($a, 'invite');
    }
    public function sendUpdated(Appointment $a): void
    {
        $this->send($a, 'updated');
    }
    public function sendCanceled(Appointment $a): void
    {
        $this->send($a, 'canceled');
    }

    private function send(Appointment $a, string $view): void
    {
        if (!config('appointments.mail.enabled')) return;

        $openUrl = $a->external_event_id ? calendar_open_url($a->external_event_id) : null;
        $addUrl = null;

        if (!$openUrl && config('appointments.mail.fallback_add_link')) {
            $addUrl = calendar_add_template_url(
                title: $a->title,
                description: $a->description ?? '',
                start: CarbonImmutable::instance($a->slot->start),
                end: CarbonImmutable::instance($a->slot->end),
                timezone: $a->slot->timezone
            );
        }

        $mailable = new \Illuminate\Mail\Mailable;
        $mailable->subject(match ($view) {
            'invite'  => 'Lời mời lịch hẹn: ' . $a->title,
            'updated' => 'Cập nhật lịch hẹn: ' . $a->title,
            'canceled' => 'Hủy lịch hẹn: ' . $a->title,
        })->view("appointments::{$view}", ['a' => $a, 'openUrl' => $openUrl, 'addUrl' => $addUrl]);

        if ($from = config('appointments.mail.from_address')) {
            $mailable->from($from, config('appointments.mail.from_name'));
        }

        foreach ($a->participants as $p) {
            \Mail::to($p->email)->queue(clone $mailable);
        }
    }
}
