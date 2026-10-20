<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('larajoom_usergroups')) {
            return;
        }
        Schema::create('larajoom_usergroups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->default(0);
            $table->string('title', 255);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('larajoom_usergroups');
    }
};
