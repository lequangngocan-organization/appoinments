<?php

declare(strict_types=1);

namespace Anlqn\Appointments;

use Carbon\CarbonInterface;

function calendar_open_url(?string $eventId): ?string
{
    return $eventId ? "https://calendar.google.com/calendar/u/0/r/eventedit/{$eventId}" : null;
}

function calendar_add_template_url(string $title, string $description, CarbonInterface $start, CarbonInterface $end, string $timezone): string
{
    $startUtc = $start->utc()->format('Ymd\THis\Z');
    $endUtc   = $end->utc()->format('Ymd\THis\Z');
    $params = http_build_query([
        'action'  => 'TEMPLATE',
        'text'    => $title,
        'details' => $description,
        'dates'   => "{$startUtc}/{$endUtc}",
        'ctz'     => $timezone,
    ]);
    return "https://calendar.google.com/calendar/render?{$params}";
}
