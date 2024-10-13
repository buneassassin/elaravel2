<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventoLiterario extends Model
{
    use HasFactory;
    

    protected $fillable = ['nombre', 'fecha', 'ubicacion'];

    public function participacionesEventos()
    {
        return $this->hasMany(ParticipacionEvento::class);
    }
}
