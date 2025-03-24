<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Resena; // Asegúrate de importar el modelo

class Resenas implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $resena;

    public function __construct($resena)
    {
        $this->resena = $resena;
    }

    public function broadcastOn()
    {
        return ['reviews'];
    }

    public function broadcastAs()
    {
        return 'ResenasCreated';
    }


    public function broadcastWith()
    {
        return [
            'resena' => $this->resena,
        ];
    }
}
