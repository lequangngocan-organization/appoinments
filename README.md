anlqn/appointments — Laravel reusable appointments (DDD-lite, Google Calendar, no .ics)

A drop-in appointments module for Laravel 9–12 that creates/reschedules/cancels Google Calendar events and lets Google send invitations/RSVP emails (no .ics).
Includes optional “brand emails”, a Google Meet toggle, and public demo routes (no auth) you can flip on/off via .env.

Features

✅ Laravel 9 / 10 / 11 / 12 compatible (PHP ≥ 8.0)

✅ Domain-driven module (DDD-lite): application/use-cases + ports/gateways

✅ Google Calendar OAuth (user consent) — Google sends invites (sendUpdates)

✅ Public demo routes (no auth) you can enable for quick testing

✅ Google Meet on/off by config

✅ Optional Laravel Mail templates (disabled by default)

✅ Works without .ics; attendees receive official Google invites

TL;DR (quick start)

# 1) Install

composer require anlqn/appointments

# 2) Publish config (optional but recommended)

php artisan vendor:publish --provider="Anlqn\Appointments\AppointmentsServiceProvider" --tag=appointments-config

# 3) Migrate (creates appointments_google_accounts table)

php artisan migrate

# 4) Set .env (demo, no auth)

echo "
APP_URL=http://127.0.0.1:8000
APPT_EXPOSE_ROUTES=true
APPT_PUBLIC_ROUTES=true
APPT_EXPOSE_OAUTH_ROUTES=true
APPT_OAUTH_PUBLIC=true

APPT_GOOGLE_ENABLED=true
APPT_GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
APPT_GOOGLE_CLIENT_SECRET=your_client_secret
APPT_GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
APPT_GOOGLE_SEND_UPDATES=all
APPT_GOOGLE_MEET=true # set false to disable Meet

APPT_MAIL_ENABLED=false
QUEUE_CONNECTION=sync
APPT_TZ=Asia/Ho_Chi_Minh
" >> .env

php artisan config:clear

# 5) Connect Google (one time)

# open in browser:

# http://127.0.0.1:8000/auth/google/redirect

# 6) Create an appointment (Google sends the invite)

curl --location 'http://127.0.0.1:8000/api/appointments' \
 --header 'Content-Type: application/json' \
 --data '{
"title": "Demo meeting",
"description": "Trao đổi 30 phút",
"sender_email": "THE_GMAIL_YOU_CONNECTED@example.com",
"receiver_email": "guest@example.com",
"start_at": "2025-10-02 09:00:00",
"end_at": "2025-10-02 09:30:00",
"timezone": "Asia/Ho_Chi_Minh"
}'

Installation
A) From Packagist (standard)
composer require anlqn/appointments

B) Local monorepo (path)

In your app’s composer.json:

{
"repositories": [
{ "type": "path", "url": "packages/anlqn/appointments" }
]
}

Then:

composer require anlqn/appointments:\* --prefer-source

The service provider auto-registers via extra.laravel.providers.

Configuration

Publish the config (optional):

php artisan vendor:publish --provider="Anlqn\Appointments\AppointmentsServiceProvider" --tag=appointments-config

You can also rely on .env only (the package reads from env).

Key .env options

# Routes

APPT_EXPOSE_ROUTES=true # expose REST API (/api/appointments)
APPT_PUBLIC_ROUTES=true # true = demo (no auth); false = secure mode (you add your guard)
APPT_ROUTES_PREFIX=api

# OAuth routes

APPT_EXPOSE_OAUTH_ROUTES=true # expose /auth/google/redirect|callback
APPT_OAUTH_PUBLIC=true # true = public; false = behind web/auth
APPT_OAUTH_PREFIX=auth

# Google OAuth / Calendar

APPT_GOOGLE_ENABLED=true
APPT_GOOGLE_CLIENT_ID=...
APPT_GOOGLE_CLIENT_SECRET=...
APPT_GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
APPT_GOOGLE_SEND_UPDATES=all # all | externalOnly | none
APPT_GOOGLE_MEET=true # true=create Meet link, false=none
APPT_ORGANIZER_EMAIL= # optional fixed organizer; else use sender_email from request

# Mail (package internal templates; OFF when using Google’s invites only)

APPT_MAIL_ENABLED=false

# Timezone & Queue

APPT_TZ=Asia/Ho_Chi_Minh
QUEUE_CONNECTION=sync # switch to redis/db + run queue:work in prod

Database

The package ships a migration for appointments_google_accounts (stores OAuth tokens). Run:

php artisan migrate

Google Cloud (OAuth) setup

Enable: Google Calendar API

OAuth consent screen:

Testing (recommended for dev), External audience if using Gmail personal

Add Test users (your email)

Scopes (Data Access → Add scopes):

https://www.googleapis.com/auth/calendar.events

openid, https://www.googleapis.com/auth/userinfo.email, https://www.googleapis.com/auth/userinfo.profile

Create OAuth Client (Web)

Authorized redirect URI: http://127.0.0.1:8000/auth/google/callback (or your APP_URL)

Fill .env with client id/secret and redirect URI.

Open http://127.0.0.1:8000/auth/google/redirect and approve.

In Testing mode you must login using an email added to Test users.

Exposed Endpoints
OAuth (public if APPT_OAUTH_PUBLIC=true)

GET /auth/google/redirect → redirects to Google

GET /auth/google/callback → saves token into appointments_google_accounts

Appointments API (public if APPT_PUBLIC_ROUTES=true)

POST /api/appointments — create (Google sends invite)

PUT /api/appointments/{id} — reschedule

DELETE /api/appointments/{id} — cancel (Google sends cancel email)

Request/validation format

start_at, end_at: Y-m-d H:i:s

timezone: any valid PHP/ICU tz (e.g. Asia/Ho_Chi_Minh)

sender_email: organizer’s email (should match a connected Google account, unless you set APPT_ORGANIZER_EMAIL)

receiver_email: attendee email

cURL examples (ready to paste)

Create

curl --location 'http://127.0.0.1:8000/api/appointments' \
--header 'Content-Type: application/json' \
--data '{
"title": "Demo meeting",
"description": "Trao đổi 30 phút",
"sender_email": "YOUR_CONNECTED_GMAIL@example.com",
"receiver_email": "guest@example.com",
"start_at": "2025-10-02 09:00:00",
"end_at": "2025-10-02 09:30:00",
"timezone": "Asia/Ho_Chi_Minh"
}'

Update (reschedule)
Replace <APPT_ID> with the id from the create response.

curl --location --request PUT 'http://127.0.0.1:8000/api/appointments/<APPT_ID>' \
--header 'Content-Type: application/json' \
--data '{
"start_at": "2025-10-02 10:00:00",
"end_at": "2025-10-02 10:30:00",
"timezone": "Asia/Ho_Chi_Minh"
}'

Delete (cancel)

curl --location --request DELETE 'http://127.0.0.1:8000/api/appointments/<APPT_ID>'

Google Meet toggle

Enable Meet links in events:

APPT_GOOGLE_MEET=true

Disable Meet:

APPT_GOOGLE_MEET=false

Clear config after change:

php artisan config:clear

(Internally, the gateway sets/removes conferenceData and conferenceDataVersion accordingly.)

Production notes

Switch queue to redis/database and run a worker:

QUEUE_CONNECTION=redis
php artisan queue:work

Keep using sendUpdates='all' to let Google send official emails.

Optional: enable package mail (APPT_MAIL_ENABLED=true) if you want branded copies in addition to Google’s invites.

For secure mode, set:

APPT_PUBLIC_ROUTES=false

…and add your auth middleware to the app routes if you expose secure endpoints.

Troubleshooting

“Redirect URI must be absolute”
Set an absolute APPT_GOOGLE_REDIRECT_URI (with http:///https://), matching the OAuth client. Ensure APP_URL is correct; run php artisan config:clear.

“Access blocked: app has not completed verification”
Use Testing mode and add your email to Test users. Scopes used are sensitive (OK in Testing), not restricted.

“Google organizer not connected”
No token found for organizer:

Use sender_email that matches the connected Google account or

Set APPT_ORGANIZER_EMAIL to the connected email or

(Package default) falls back to the first stored account.
Also make sure you opened /auth/google/redirect and a row exists in appointments_google_accounts.

“Call to undefined method PendingChain::dispatchAfterResponse()”
Use job dispatching like:

CreateCalendarEventJob::withChain([
new SendInvitationEmailsJob($id),
])->dispatch($id)->afterCommit(); // (optional) ->afterResponse()

“Event::setStart(): array given”
Google client requires EventDateTime:

$dt = new \Google\Service\Calendar\EventDateTime();
$dt->setDateTime($iso); $dt->setTimeZone($tz);
$event->setStart($dt);

Uninstall
composer remove anlqn/appointments

Optionally drop the table:

php artisan tinker

> > > Schema::dropIfExists('appointments_google_accounts');

License

MIT
