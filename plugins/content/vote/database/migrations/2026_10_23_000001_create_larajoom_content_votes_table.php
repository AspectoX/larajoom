<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('larajoom_content_votes')) {
            Schema::create('larajoom_content_votes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('content_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('ip', 45)->nullable();
                $table->tinyInteger('rating')->unsigned();   // 1..5
                $table->timestamp('created_at')->useCurrent();

                $table->index(['content_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_content_votes');
    }
};
