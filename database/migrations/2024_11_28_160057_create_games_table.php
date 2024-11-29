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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('word'); // Palabra oculta
            $table->string('masked_word'); // Palabra enmascarada
            $table->integer('attempts')->default(0); // Intentos realizados
            $table->integer('max_attempts')->default(5); // Máximos intentos permitidos
            $table->boolean('is_completed')->default(false); // Si el juego terminó
            $table->boolean('is_won')->nullable(); // Si el jugador ganó
            $table->text('attempted_letters')->nullable();
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
        Schema::dropIfExists('games');
    }
};
