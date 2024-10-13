<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Libreria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'ubicacion'];

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }
}
