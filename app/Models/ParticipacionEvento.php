<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipacionEvento extends Model
{
    use HasFactory;
    
    protected $fillable = ['evento_id', 'autor_id'];

    public function evento()
    {
        return $this->belongsTo(EventoLiterario::class);
    }

    public function autor()
    {
        return $this->belongsTo(Autor::class);
    }
}
