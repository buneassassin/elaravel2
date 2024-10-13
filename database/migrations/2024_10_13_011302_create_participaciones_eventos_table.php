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
        Schema::create('participacion_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('evento_literarios');
            $table->foreignId('autor_id')->constrained('autors');
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
        Schema::dropIfExists('participaciones_eventos');
    }
};
