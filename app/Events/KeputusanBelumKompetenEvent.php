<?php

namespace App\Events;

use App\Models\KeputusanSertifikasi;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KeputusanBelumKompetenEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $keputusan;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(KeputusanSertifikasi $keputusan)
    {
        $this->keputusan = $keputusan;
        $this->timestamp = now();
    }
}
