<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory;

    protected $table = 'attempts';

    protected $fillable = ['user_id','game_id', 'word_attempted', 'feedback'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function juego()
    {
        return $this->belongsTo(Juego::class);
    }
    
}
