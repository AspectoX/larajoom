<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('larajoom_media')) {
            Schema::create('larajoom_media', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('path', 500);
                $table->string('disk', 50)->default('public');
                $table->string('mime_type', 100)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->unsignedBigInteger('folder_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index(['folder_id', 'name']);
                $table->index('path');
            });
        }
        
        if (!Schema::hasTable('larajoom_media_folders')) {
            Schema::create('larajoom_media_folders', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('path', 500);
                $table->string('disk', 50)->default('public');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('ordering')->default(0);
                $table->timestamps();
                
                $table->index(['parent_id', 'name']);
                $table->index('path');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_media');
        Schema::dropIfExists('larajoom_media_folders');
    }
};