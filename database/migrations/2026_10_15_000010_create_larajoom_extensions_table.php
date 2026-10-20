<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable("larajoom_extensions")) {
            return;
        }
        Schema::create("larajoom_extensions", function (Blueprint $table) {
            $table->increments("extension_id");
            $table->integer("asset_id")->default(0);
            $table->string("name", 100);
            $table->string("type", 20);
            $table->string("element", 100);
            $table->string("folder", 50)->default("");
            $table->tinyInteger("client_id")->default(1);
            $table->tinyInteger("enabled")->default(1);
            $table->tinyInteger("access")->default(1);
            $table->integer("ordering")->default(0);
            $table->json("params")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("larajoom_extensions");
    }
};
