<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nombre del rol
            $table->timestamps();
        });

        // Agregar la columna role_id en la tabla users para la relación
       
    }

    public function down()
    {
    
        Schema::dropIfExists('roles');
    }
};
