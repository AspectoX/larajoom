<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('larajoom_user_mfa')) {
            Schema::create('larajoom_user_mfa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('method', 32);              // totp | yubikey | webauthn | passwordless
                $table->text('secret')->nullable();        // secreto TOTP cifrado / clave pública WebAuthn
                $table->string('label', 100)->nullable();  // descripción del dispositivo
                $table->boolean('enabled')->default(1);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'method']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_user_mfa');
    }
};
