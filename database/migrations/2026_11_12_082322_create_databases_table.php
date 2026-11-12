<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notion_databases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('قاعدة بيانات');
            $table->json('properties')->nullable(); // تعريف الأعمدة: [{name, type, options}]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notion_databases');
    }
};
