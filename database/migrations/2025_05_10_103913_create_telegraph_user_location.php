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
        Schema::create('telegraph_user_locations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unique(); // ID пользователя Telegram

            $table->foreignId('user_id')->references('user_id')->on('telegraph_users')->onDelete('cascade');;
            $table->integer('city_id')->nullable();
            $table->string('station_id')->nullable();
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
