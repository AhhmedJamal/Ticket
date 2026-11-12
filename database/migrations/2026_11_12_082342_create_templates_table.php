<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon', 20)->default('📋');
            $table->string('category', 50)->default('general'); // general, project, meeting, notes
            $table->json('blocks_data'); // مصفوفة البلوكات الجاهزة
            $table->boolean('is_default')->default(false); // قوالب المنصة الافتراضية
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
