<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;
    protected $fillable = ['libreria_id', 'libro_id', 'cantidad'];

    public function libreria()
    {
        return $this->belongsTo(Libreria::class);
    }

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }
}
