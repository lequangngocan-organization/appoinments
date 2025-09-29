<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Google\TokenStore;

interface TokenStore
{
    public function getByEmail(string $email): ?array;

    public function getFirst(): ?array;

    public function save(string $email, string $googleUserId, array $token, ?int $expiresIn): void;
}
