<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LectorCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $lector;

    public function __construct($lector)
    {
         $this->lector = $lector;
    }

    // Indica en qué canal se transmitirá el evento
    public function broadcastOn()
    {
         return ['lectores_channel'];
    }

    // (Opcional) Puedes definir un nombre personalizado para el evento
    public function broadcastAs()
    {
         return 'lector_created';
    }
}
