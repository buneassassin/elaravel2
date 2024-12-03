<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juego extends Model
{
    use HasFactory;

    protected $table = 'juegos';


    protected $fillable = [
        'user_id',
        'word',
        'attempts_used',
        'is_completed',
        'is_won',
    ];

    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'game_id');
    }
    
}
