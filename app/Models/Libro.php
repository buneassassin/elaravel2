<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Libro extends Model
{
    use HasFactory;
    

    protected $fillable = ['titulo', 'genero', 'autor_id'];

    public function autor()
    {
        return $this->belongsTo(Autor::class);
    }

    public function publicaciones()
    {
        return $this->hasMany(Publicacion::class);
    }

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class);
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }
}
