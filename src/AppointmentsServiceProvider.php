<?php

declare(strict_types=1);

namespace Anlqn\Appointments;

use Illuminate\Support\ServiceProvider;
use Anlqn\Appointments\Application\Ports\{CalendarGateway, Mailer};
use Anlqn\Appointments\Domain\Repositories\AppointmentRepository;
use Anlqn\Appointments\Infrastructure\{Calendar\GoogleCalendarGateway, Mail\LaravelMailer};
use Anlqn\Appointments\Infrastructure\Persistence\EloquentAppointmentRepository;
use Anlqn\Appointments\Domain\Events\{AppointmentScheduled, AppointmentRescheduled, AppointmentCanceled};
use Anlqn\Appointments\Support\Jobs\{CreateCalendarEventJob, UpdateCalendarEventJob};
use Anlqn\Appointments\Google\TokenStore\{TokenStore, EloquentTokenStore};
use Anlqn\Appointments\Support\Jobs\{CancelCalendarEventJob};

final class AppointmentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Support/Config/appointments.php', 'appointments');

        $this->app->bind(AppointmentRepository::class, EloquentAppointmentRepository::class);
        $this->app->bind(CalendarGateway::class, GoogleCalendarGateway::class);
        $this->app->bind(Mailer::class, LaravelMailer::class);
        $this->app->bind(TokenStore::class, EloquentTokenStore::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/Support/Config/appointments.php' => config_path('appointments.php'),
        ], 'appointments-config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/Support/Mail/views', 'appointments');

        // (Optional) plug-and-play API
        if (file_exists(__DIR__ . '/Support/routes.php') && config('appointments.expose_routes', true)) {
            $this->loadRoutesFrom(__DIR__ . '/Support/routes.php');
        }

        // Domain events → jobs
        \Event::listen(
            AppointmentScheduled::class,
            fn($e) =>
            CreateCalendarEventJob::dispatch($e->appt->id)->afterCommit()
        );

        \Event::listen(
            AppointmentRescheduled::class,
            fn($e) =>
            UpdateCalendarEventJob::dispatch($e->appt->id)->afterCommit()
        );

        \Event::listen(AppointmentCanceled::class, function ($e) {
            CancelCalendarEventJob::dispatch($e->appt->id)
                ->afterCommit()
                ->afterResponse();
        });
    }
}
