<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('larajoom_privacy_requests')) {
            Schema::create('larajoom_privacy_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('email', 155);
                $table->string('type', 16);            // export | remove
                $table->enum('status', ['pending', 'completed'])->default('pending');
                $table->timestamp('requested_at')->useCurrent();
                $table->timestamp('completed_at')->nullable();
                $table->text('payload')->nullable();   // JSON exportado
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_privacy_requests');
    }
};
