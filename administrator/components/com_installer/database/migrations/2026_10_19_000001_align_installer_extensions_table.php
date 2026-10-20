<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('larajoom_extensions', function (Blueprint $table) {
            if (!Schema::hasColumn('larajoom_extensions', 'package_id')) {
                $table->integer('package_id')->default(0)->after('asset_id');
            }
            if (!Schema::hasColumn('larajoom_extensions', 'protected')) {
                $table->tinyInteger('protected')->default(0)->after('access');
            }
            if (!Schema::hasColumn('larajoom_extensions', 'locked')) {
                $table->tinyInteger('locked')->default(0)->after('protected');
            }
            if (!Schema::hasColumn('larajoom_extensions', 'manifest_cache')) {
                $table->json('manifest_cache')->nullable()->after('params');
            }
            if (!Schema::hasColumn('larajoom_extensions', 'state')) {
                $table->integer('state')->default(0)->after('ordering');
            }
            if (!Schema::hasColumn('larajoom_extensions', 'note')) {
                $table->string('note', 255)->nullable()->after('state');
            }
        });
    }

    public function down(): void
    {
        Schema::table('larajoom_extensions', function (Blueprint $table) {
            foreach (['package_id', 'protected', 'locked', 'manifest_cache', 'state', 'note'] as $col) {
                if (Schema::hasColumn('larajoom_extensions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
