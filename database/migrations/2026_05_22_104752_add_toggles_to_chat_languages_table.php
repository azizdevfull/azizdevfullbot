<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_languages', function (Blueprint $table) {
            $table->boolean('ai_enabled')->default(true)->after('persona_id');
            $table->boolean('learning_enabled')->default(true)->after('ai_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('chat_languages', function (Blueprint $table) {
            $table->dropColumn(['ai_enabled', 'learning_enabled']);
        });
    }
};
