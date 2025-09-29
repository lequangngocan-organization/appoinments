<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments_google_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('google_user_id')->index();
            $t->string('email')->unique();
            $t->text('access_token_json');
            $t->string('refresh_token')->nullable();
            $t->timestamp('token_expires_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('appointments_google_accounts');
    }
};
