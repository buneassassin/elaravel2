<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    use HasFactory;
    
    protected $fillable = ['libro_id', 'lector_id', 'calificacion', 'comentario'];

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }

    public function lector()
    {
        return $this->belongsTo(Lector::class);
    }
}
