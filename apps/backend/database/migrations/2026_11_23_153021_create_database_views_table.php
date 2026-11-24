<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('database_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notion_database_id')->constrained('notion_databases')->cascadeOnDelete();
            $table->string('name', 100)->default('عرض افتراضي');
            $table->enum('type', ['table', 'board', 'list', 'gallery'])->default('table');
            $table->json('filter_config')->nullable();
            $table->json('sort_config')->nullable();
            $table->json('visible_properties')->nullable();
            $table->integer('position')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_views');
    }
};
