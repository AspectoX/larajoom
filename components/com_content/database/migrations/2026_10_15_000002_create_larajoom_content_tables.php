<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('larajoom_categories')) {
            Schema::create('larajoom_categories', function (Blueprint $table) {
                $table->id();
                $table->string('title', 255);
                $table->string('alias', 255)->unique();
                $table->text('description')->nullable();
                $table->boolean('published')->default(true);
                $table->unsignedBigInteger('access')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('larajoom_content')) {
            Schema::create('larajoom_content', function (Blueprint $table) {
                $table->id();
                $table->string('title', 255);
                $table->string('alias', 255)->unique();
                $table->text('introtext')->nullable();
                $table->longText('fulltext')->nullable();
                $table->boolean('published')->default(true);
                $table->unsignedBigInteger('catid')->default(0);
                $table->unsignedBigInteger('created_by')->default(0);
                $table->unsignedBigInteger('access')->default(1);
                $table->integer('hits')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_content');
        Schema::dropIfExists('larajoom_categories');
    }
};
