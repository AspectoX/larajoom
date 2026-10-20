<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('larajoom_extensions', function (Blueprint $table) {
            // El plugin vote guarda sus params (position, show_total_votes) en larajoom_extensions.params JSON
        });
    }

    public function down(): void
    {
        // Sin cambios estructurales en DB, solo params
    }
};
