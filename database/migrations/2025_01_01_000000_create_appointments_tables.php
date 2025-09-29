<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $t) {
            $t->ulid('id')->primary();
            $t->string('title');
            $t->text('description')->nullable();
            $t->dateTimeTz('start_at');
            $t->dateTimeTz('end_at');
            $t->string('timezone', 64);
            $t->string('status', 32)->default('scheduled');
            $t->string('external_event_id')->nullable();
            $t->timestamps();
        });

        Schema::create('appointment_participants', function (Blueprint $t) {
            $t->id();
            $t->ulid('appointment_id')->index();
            $t->string('role', 32); // sender|receiver
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('email');
            $t->string('display_name')->nullable();
            $t->boolean('is_required')->default(true);
            $t->string('response_status', 32)->nullable(); // accepted|declined|tentative|needsAction
            $t->timestamps();

            $t->foreign('appointment_id')->references('id')->on('appointments')->cascadeOnDelete();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('appointment_participants');
        Schema::dropIfExists('appointments');
    }
};
