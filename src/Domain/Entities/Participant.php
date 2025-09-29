<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Domain\Entities;

final class Participant
{
    public function __construct(
        public readonly string $role, // sender|receiver
        public readonly int|string|null $user_id,
        public readonly string $email,
        public readonly ?string $display_name = null,
        public readonly bool $is_required = true,
        public ?string $response_status = null
    ) {}
}
