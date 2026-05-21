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
        Schema::table('chat_languages', function (Blueprint $table) {
            $table->string('address_form', 5)->default('siz')->after('is_manual');
        });
    }

    public function down(): void
    {
        Schema::table('chat_languages', function (Blueprint $table) {
            $table->dropColumn('address_form');
        });
    }
};
