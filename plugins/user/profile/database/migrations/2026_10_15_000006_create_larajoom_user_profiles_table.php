<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de perfiles de usuario (equivalente a #__user_profiles de Joomla)
        if (!Schema::hasTable('larajoom_user_profile')) {
            Schema::create('larajoom_user_profile', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->string('profile_key', 100);
                $table->text('profile_value')->nullable();
                $table->integer('ordering')->default(0);
                $table->primary(['user_id', 'profile_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_user_profile');
    }
};
