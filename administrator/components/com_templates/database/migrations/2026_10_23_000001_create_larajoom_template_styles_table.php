<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('larajoom_template_styles')) {
            Schema::create('larajoom_template_styles', function (Blueprint $table) {
                $table->id();
                $table->string('template', 50);          // nombre del template (ej. atum)
                $table->string('title', 150);            // nombre del estilo
                $table->text('params')->nullable();      // JSON con opciones del estilo
                $table->boolean('home')->default(0);     // 1 = estilo predeterminado / activo
                $table->timestamps();

                $table->index(['template', 'home']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_template_styles');
    }
};
