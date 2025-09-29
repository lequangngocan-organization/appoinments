<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Infrastructure\OAuth;

use Illuminate\Database\Eloquent\Model;

final class GoogleAccount extends Model
{
    protected $table = 'appointments_google_accounts';
    protected $fillable = ['google_user_id', 'email', 'access_token_json', 'refresh_token', 'token_expires_at'];
    protected $casts = ['token_expires_at' => 'datetime'];
}
