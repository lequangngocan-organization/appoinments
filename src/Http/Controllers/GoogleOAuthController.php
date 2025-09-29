<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Google\Service\Oauth2;
use Anlqn\Appointments\Google\ClientFactory;
use Anlqn\Appointments\Google\TokenStore\TokenStore;

final class GoogleOAuthController extends Controller
{
    public function redirect(Request $r)
    {
        $client = ClientFactory::make();
        return redirect()->away($client->createAuthUrl());
    }

    public function callback(Request $r, TokenStore $store)
    {
        $code = $r->query('code');
        abort_unless($code, 400, 'Missing code');

        $client = ClientFactory::make();
        $token  = $client->fetchAccessTokenWithAuthCode((string)$code);
        if (isset($token['error'])) abort(400, 'Google OAuth error: ' . $token['error']);
        $client->setAccessToken($token);

        $payload = $client->verifyIdToken($token['id_token'] ?? null) ?: [];
        $email   = $payload['email'] ?? null;
        $sub     = $payload['sub'] ?? null;

        if (!$email || !$sub) {
            $oauth2 = new Oauth2($client);
            $me     = $oauth2->userinfo->get();
            $email  = $me->email;
            $sub    = $me->id;
        }

        $store->save($email, $sub, $token, $token['expires_in'] ?? null);

        return response('Connected Google Calendar for ' . $email, 200);
    }
}
