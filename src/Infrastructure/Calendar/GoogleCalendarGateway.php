<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Infrastructure\Calendar;

use Anlqn\Appointments\Application\Ports\CalendarGateway;
use Anlqn\Appointments\Domain\Entities\Appointment;
use Anlqn\Appointments\Google\ClientFactory;
use Anlqn\Appointments\Google\TokenStore\TokenStore;
use Google\Service\Calendar as GoogleCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final class GoogleCalendarGateway implements CalendarGateway
{
    public function __construct(private TokenStore $store) {}

    private function service(?string $organizerEmail = null): GoogleCalendar
    {
        // Determine which Google account to use
        $email = $organizerEmail ?: (string) config('appointments.google.organizer_email');

        $acc = $email ? $this->store->getByEmail($email) : null;
        if (!$acc) {
            // Optional fallback: first connected account
            $acc = $this->store->getFirst();
        }
        if (!$acc) {
            throw new \RuntimeException('Google organizer not connected');
        }

        // Build client with absolute redirect URI (either from config or route name)
        $redirectUri = config('appointments.google.redirect_uri')
            ?: route('appointments.oauth.callback');

        $client = ClientFactory::make($redirectUri);

        // Refresh token if expired (and refresh_token exists)
        $token = $acc['token'];
        if (!empty($acc['refresh']) && !empty($acc['expires_at']) && $acc['expires_at']->isPast()) {
            $client->fetchAccessTokenWithRefreshToken($acc['refresh']);
            $token = $client->getAccessToken();
            // Persist newly issued token (keep same email)
            $this->store->save($acc['email'], $token['sub'] ?? '', $token, $token['expires_in'] ?? null);
        }

        $client->setAccessToken($token);
        return new GoogleCalendar($client);
    }

    public function create(Appointment $a): ?string
    {
        if (!config('appointments.google.enabled')) return null;

        $svc = $this->service($a->sender()?->email);
        $withMeet = (bool) config('appointments.meet.enabled');

        $payload = [
            'summary'     => $a->title,
            'description' => $a->description ?? '',
            'start' => [
                'dateTime' => CarbonImmutable::instance($a->slot->start)->tz($a->slot->timezone)->toIso8601String(),
                'timeZone' => $a->slot->timezone,
            ],
            'end' => [
                'dateTime' => CarbonImmutable::instance($a->slot->end)->tz($a->slot->timezone)->toIso8601String(),
                'timeZone' => $a->slot->timezone,
            ],
            'attendees' => array_map(fn($p) => ['email' => $p->email], $a->participants),
        ];

        if ($withMeet) {
            $payload['conferenceData'] = [
                'createRequest' => [
                    'requestId' => (string) \Illuminate\Support\Str::uuid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ];
        }

        $event = new GoogleCalendar\Event($payload);

        $params = [
            'sendUpdates' => config('appointments.google.send_updates', 'all'),
        ];

        if ($withMeet) $params['conferenceDataVersion'] = 1;

        $inserted = $svc->events->insert('primary', $event, $params);

        return $inserted->id ?? null;
    }

    public function update(Appointment $a): void
    {
        if (!config('appointments.google.enabled') || !$a->external_event_id) {
            return;
        }

        // Lấy service theo organizer (ưu tiên sender_email, fallback config organizer_email)
        $svc = $this->service($a->sender()?->email);

        // Lấy event hiện tại
        $event = $svc->events->get('primary', $a->external_event_id);

        // Cập nhật tiêu đề & mô tả
        $event->setSummary($a->title);
        $event->setDescription($a->description ?? '');

        // Chuẩn hóa thời gian & timezone
        $startIso = \Carbon\CarbonImmutable::instance($a->slot->start)
            ->tz($a->slot->timezone)->toIso8601String();
        $endIso = \Carbon\CarbonImmutable::instance($a->slot->end)
            ->tz($a->slot->timezone)->toIso8601String();

        // ⬇️ setStart/setEnd phải là EventDateTime (không phải array)
        $startDT = new \Google\Service\Calendar\EventDateTime();
        $startDT->setDateTime($startIso);
        $startDT->setTimeZone($a->slot->timezone);
        $event->setStart($startDT);

        $endDT = new \Google\Service\Calendar\EventDateTime();
        $endDT->setDateTime($endIso);
        $endDT->setTimeZone($a->slot->timezone);
        $event->setEnd($endDT);

        // Bật/tắt Google Meet theo config
        $withMeet = (bool) config('appointments.google.meet.enabled', true);
        $params = [
            'sendUpdates' => (string) config('appointments.google.send_updates', 'all'),
        ];

        if ($withMeet) {
            $hasMeet = $event->getConferenceData() && $event->getConferenceData()->getConferenceId();
            if (!$hasMeet) {
                $event->setConferenceData(new \Google\Service\Calendar\ConferenceData([
                    'createRequest' => [
                        'requestId' => (string) \Illuminate\Support\Str::uuid(),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    ],
                ]));
            }
            $params['conferenceDataVersion'] = 1;
        } else {
            if ($event->getConferenceData()) {
                // Gỡ link Meet
                $event->setConferenceData(null);
                $params['conferenceDataVersion'] = 1;
            }
        }

        // Patch event
        $svc->events->patch('primary', $a->external_event_id, $event, $params);
    }


    public function cancel(Appointment $a): void
    {
        if (!config('appointments.google.enabled') || !$a->external_event_id) return;

        $svc = $this->service($a->sender()?->email);
        $svc->events->delete('primary', $a->external_event_id, [
            'sendUpdates' => (string) config('appointments.google.send_updates', 'all'),
        ]);
    }
}
