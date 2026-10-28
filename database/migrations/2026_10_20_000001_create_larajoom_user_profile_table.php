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

            // Estado y preferencias
            $table->boolean('block')->default(0);
            $table->boolean('sendEmail')->default(1);
            $table->text('params')->nullable();

            // Datos de la cuenta (username)
            $table->string('username', 255)->nullable()->after('user_id');

            // Fechas
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
