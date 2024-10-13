<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Editorial extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'pais'];

    public function publicaciones()
    {
        return $this->hasMany(Publicacion::class);
    }
}
