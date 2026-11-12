<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notion_database_id')->constrained('notion_databases')->cascadeOnDelete();
            $table->json('values')->nullable(); // {column_name: value}
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_rows');
    }
};
