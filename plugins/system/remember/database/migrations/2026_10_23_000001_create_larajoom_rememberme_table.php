<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('larajoom_rememberme')) {
            Schema::create('larajoom_rememberme', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('series', 64);        // identificador de la serie
                $table->string('token', 64);         // hash del token actual
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->index(['user_id', 'series']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_rememberme');
    }
};
