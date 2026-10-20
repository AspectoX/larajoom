<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('larajoom_users')) {
            Schema::create('larajoom_users', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 100);
                $table->string('username', 255);
                $table->string('email', 155);
                $table->string('password', 255);
                $table->unsignedBigInteger('group_id')->default(8);
                $table->boolean('activated')->default(1);
                $table->timestamp('lastLoginDate')->nullable();
                $table->text('params')->nullable();
                $table->timestamp('registerDate')->useCurrent();
                $table->timestamps();
                $table->rememberToken();
                $table->index(['email']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_users');
    }
};
