<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('larajoom_modules')) {
            Schema::create('larajoom_modules', function (Blueprint $table) {
                $table->id();
                $table->string('title', 150);
                $table->string('module', 100);       // e.g. 'menu', 'breadcrumbs', 'custom'
                $table->string('position', 50);      // e.g. 'top', 'right', 'footer'
                $table->boolean('published')->default(true);
                $table->integer('ordering')->default(0);
                $table->boolean('showtitle')->default(true);
                $table->json('params')->nullable();  // Custom module parameters
                $table->unsignedBigInteger('access')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_modules');
    }
};
