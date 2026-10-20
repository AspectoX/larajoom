<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perfil del usuario (equivalente a los datos de #__users de Joomla 6).
 *
 * La tabla guarda los datos de perfil de cada usuario: datos de la cuenta,
 * estado, fechas y el avatar (propio de LaraJoom).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('larajoom_user_profile')) {
            return;
        }

        Schema::create('larajoom_user_profile', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();

            // Datos de la cuenta
            $table->string('name', 100);
            $table->string('username', 255)->nullable();
            $table->string('email', 155)->nullable();
            $table->string('password', 255)->nullable();

            // Estado y preferencias
            $table->boolean('block')->default(0);
            $table->boolean('activated')->default(1);
            $table->boolean('sendEmail')->default(1);
            $table->text('params')->nullable();

            // Fechas
            $table->timestamp('registerDate')->useCurrent();
            $table->timestamp('lastvisitDate')->nullable();
            $table->timestamp('lastResetTime')->nullable();
            $table->unsignedInteger('resetCount')->default(0);

            // Avatar (propio de LaraJoom, no está en Joomla 6)
            $table->string('avatar', 255)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_user_profile');
    }
};
