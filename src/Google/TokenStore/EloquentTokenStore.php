<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Google\TokenStore;

use Anlqn\Appointments\Infrastructure\OAuth\GoogleAccount;

final class EloquentTokenStore implements TokenStore
{
    public function getByEmail(string $email): ?array
    {
        $acc = GoogleAccount::where('email', $email)->first();
        return $this->wrap($acc);
    }

    public function getFirst(): ?array
    {
        $acc = GoogleAccount::first();
        return $this->wrap($acc);
    }

    public function save(string $email, string $googleUserId, array $token, ?int $expiresIn): void
    {
        $acc = GoogleAccount::updateOrCreate(
            ['email' => $email],
            [
                'google_user_id'     => $googleUserId,
                'access_token_json'  => json_encode($token),
                'refresh_token'      => $token['refresh_token'] ?? null,
                'token_expires_at'   => now()->addSeconds($expiresIn ?? 0),
            ]
        );
    }

    private function wrap(?GoogleAccount $acc): ?array
    {
        if (!$acc) return null;
        return [
            'email'     => $acc->email,
            'token'     => json_decode($acc->access_token_json, true) ?: [],
            'refresh'   => $acc->refresh_token,
            'expires_at' => $acc->token_expires_at,
        ];
    }
}
