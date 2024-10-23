<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publicacion extends Model
{
    use HasFactory;
    protected $fillable = ['libro_id', 'editorial_id', 'fecha_publicacion'];

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }

    public function editorial()
    {
        return $this->belongsTo(Editorial::class);
    }
    // En el modelo Libro
    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }

    public function publicaciones()
    {
        return $this->hasMany(Publicacion::class);
    }
}
