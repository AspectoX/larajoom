<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('larajoom_menu_types')) {
            Schema::table('larajoom_menu_types', function (Blueprint $table) {
                if (!Schema::hasColumn('larajoom_menu_types', 'description')) {
                    $table->string('description', 255)->nullable()->after('title');
                }
                if (!Schema::hasColumn('larajoom_menu_types', 'client_id')) {
                    $table->tinyInteger('client_id')->default(0)->after('description');
                }
                if (!Schema::hasColumn('larajoom_menu_types', 'ordering')) {
                    $table->integer('ordering')->default(0)->after('client_id');
                }
            });
        }

        if (Schema::hasTable('larajoom_menu')) {
            Schema::table('larajoom_menu', function (Blueprint $table) {
                if (!Schema::hasColumn('larajoom_menu', 'level')) {
                    $table->integer('level')->default(1)->after('parent_id');
                }
                if (!Schema::hasColumn('larajoom_menu', 'icon')) {
                    $table->string('icon', 255)->nullable()->after('home');
                }
                if (!Schema::hasColumn('larajoom_menu', 'show_icon')) {
                    $table->boolean('show_icon')->default(false)->after('home');
                }
                if (!Schema::hasColumn('larajoom_menu', 'access')) {
                    $table->unsignedBigInteger('access')->default(1)->after('show_icon');
                }
            });

            // menutype must be a plain index, not unique (many items share one menutype)
            $indexes = Schema::getIndexes('larajoom_menu');
            foreach ($indexes as $index) {
                if ($index['unique'] && in_array('menutype', $index['columns'])) {
                    Schema::table('larajoom_menu', function (Blueprint $table) use ($index) {
                        $table->dropUnique($index['name']);
                    });
                    Schema::table('larajoom_menu', function (Blueprint $table) {
                        $table->index('menutype');
                    });
                    break;
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally a no-op: this migration only aligns columns and indexes.
    }
};
