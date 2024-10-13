<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autor extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'nacionalidad'];

    public function libros()
    {
        return $this->hasMany(Libro::class);
    }

    public function participacionesEventos()
    {
        return $this->hasMany(ParticipacionEvento::class);
    }
}
