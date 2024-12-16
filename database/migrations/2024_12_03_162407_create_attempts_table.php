<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('juegos')->onDelete('cascade'); // Relación con la tabla games
            $table->string('word_attempted' , 20); // Palabra intentada, de 5 caracteres
            $table->json('feedback'); // Feedback del intento (correcta, incorrecta, mal ubicada)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attempts');
    }
};
