<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;
      // Define the table name if it's not pluralized by default
      protected $table = 'tokens';

      // Specify the fields that can be mass assigned
      protected $fillable = [
          'token3',
          'token4',
      ];
  
      // Optionally, you can disable timestamps if you're not using them
      public $timestamps = true;
}
