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
        Schema::create('telegraph_user_location', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unique(); // ID пользователя Telegram
            $table->string('state')->nullable();     // Текущее состояние (например, 'awaiting_login')
            $table->text('data')->nullable();        // Дополнительные данные (например, логин)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegraph_user_location');
    }
};
