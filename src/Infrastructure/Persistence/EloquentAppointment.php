<?php

declare(strict_types=1);

namespace Anlqn\Appointments\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

final class EloquentAppointment extends Model
{
    use HasUlids;
    protected $table = 'appointments';
    protected $fillable = [
        'id',
        'title',
        'description',
        'start_at',
        'end_at',
        'timezone',
        'status',
        'external_event_id',
    ];
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function participants()
    {
        return $this->hasMany(EloquentParticipant::class, 'appointment_id');
    }
}

final class EloquentParticipant extends Model
{
    protected $table = 'appointment_participants';
    protected $fillable = [
        'appointment_id',
        'role',
        'user_id',
        'email',
        'display_name',
        'is_required',
        'response_status',
    ];
}
