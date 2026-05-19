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
        Schema::create('business_connections', function (Blueprint $table) {
            $table->id();
            $table->string('connection_id')->unique();
            $table->bigInteger('telegram_user_id');
            $table->bigInteger('user_chat_id')->nullable();
            $table->boolean('can_reply')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_connections');
    }
};
