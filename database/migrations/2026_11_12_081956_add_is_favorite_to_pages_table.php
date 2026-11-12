<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('is_favorite')->default(false)->after('icon');
            $table->string('cover_color', 50)->nullable()->after('is_favorite'); // لون الغلاف بالتدرج
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['is_favorite', 'cover_color']);
        });
    }
};
