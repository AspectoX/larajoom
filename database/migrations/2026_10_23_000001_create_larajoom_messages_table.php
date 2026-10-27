<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('larajoom_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_user_id')->index();
            $table->unsignedBigInteger('to_user_id')->index();
            $table->string('subject', 255);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('read_at')->nullable();

            $table->foreign('from_user_id')->references('id')->on('larajoom_users')->onDelete('cascade');
            $table->foreign('to_user_id')->references('id')->on('larajoom_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_messages');
    }
};
