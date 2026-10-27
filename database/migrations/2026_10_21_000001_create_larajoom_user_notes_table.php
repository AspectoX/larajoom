<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('larajoom_user_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->text('note_text');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('larajoom_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_user_notes');
    }
};
