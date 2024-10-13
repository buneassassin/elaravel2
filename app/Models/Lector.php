<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lector extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'email'];

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class);
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }
}
