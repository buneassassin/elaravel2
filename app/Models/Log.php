<?php
namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Log extends Model
{
    protected $connection = 'mongodb'; // Se conecta a MongoDB
    protected $collection = 'logs'; // Nombre de la colección
    protected $fillable = ['user_id', 'verbo', 'ruta', 'dato', 'fecha'];
 
    
}