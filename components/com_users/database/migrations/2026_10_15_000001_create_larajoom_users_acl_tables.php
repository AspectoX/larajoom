<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('larajoom_usergroups')) {
            Schema::create('larajoom_usergroups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('parent_id')->default(0);
                $table->string('title', 100);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('larajoom_user_usergroup_map')) {
            Schema::create('larajoom_user_usergroup_map', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('group_id');
                $table->primary(['user_id', 'group_id']);
            });
        }

        if (!Schema::hasTable('larajoom_viewlevels')) {
            Schema::create('larajoom_viewlevels', function (Blueprint $table) {
                $table->id();
                $table->string('title', 100);
                $table->json('rules'); // Array of group IDs with access
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('larajoom_viewlevels');
        Schema::dropIfExists('larajoom_user_usergroup_map');
        Schema::dropIfExists('larajoom_usergroups');
    }
};
