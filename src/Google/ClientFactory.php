<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Google;

use Google\Client;

final class ClientFactory
{
    public static function make(?string $redirectUri = null): Client
    {
        $client = new Client();
        $client->setClientId((string)config('appointments.google.client_id'));
        $client->setClientSecret((string)config('appointments.google.client_secret'));
        $client->setRedirectUri($redirectUri ?? (string)config('appointments.google.redirect_uri'));
        $client->setAccessType((string)config('appointments.google.access_type', 'offline'));
        $client->setPrompt((string)config('appointments.google.prompt', 'consent'));
        $client->setIncludeGrantedScopes(true);
        $client->setScopes((array)config('appointments.google.scopes', []));
        return $client;
    }
}
