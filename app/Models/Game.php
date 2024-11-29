<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;
    /*Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('word'); // Palabra oculta
            $table->string('masked_word'); // Palabra enmascarada
            $table->integer('attempts')->default(0); // Intentos realizados
            $table->integer('max_attempts')->default(5); // Máximos intentos permitidos
            $table->boolean('is_completed')->default(false); // Si el juego terminó
            $table->boolean('is_won')->nullable(); // Si el jugador ganó
            $table->timestamps();
        });*/
    
    protected $fillable = [
        'user_id',
        'word',
        'masked_word',
        'attempts',
        'max_attempts',
        'is_completed',
        'is_won',
        'attempted_letters'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
