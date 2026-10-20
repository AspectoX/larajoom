<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('larajoom_menu_types')) {
            Schema::create('larajoom_menu_types', function (Blueprint $table) {
                $table->id();
                $table->string('menutype', 100)->unique();
                $table->string('title', 150);
                $table->string('description', 255)->nullable();
                $table->tinyInteger('client_id')->default(0); // 0 = site, 1 = admin
                $table->integer('ordering')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('larajoom_menu')) {
            Schema::create('larajoom_menu', function (Blueprint $table) {
                $table->id();
                $table->string('menutype', 100)->index();
                $table->string('title', 255);
                $table->string('alias', 255)->nullable();
                $table->string('link', 255)->nullable();
                $table->string('type', 50)->default('url');
                $table->boolean('published')->default(true);
                $table->unsignedBigInteger('parent_id')->default(0)->index();
                $table->integer('level')->default(1);
                $table->integer('ordering')->default(0);
                $table->boolean('home')->default(false);
                $table->boolean('show_icon')->default(false);
                $table->string('icon', 255)->nullable();
                $table->unsignedBigInteger('access')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_menu');
        Schema::dropIfExists('larajoom_menu_types');
    }
};
