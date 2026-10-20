<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('larajoom_action_logs')) {
            return;
        }
        Schema::create('larajoom_action_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 50); // create, update, delete, export, etc.
            $table->string('extension', 100)->nullable(); // com_content, com_users
            $table->string('item_id', 50)->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('extra')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_action_logs');
    }
};
