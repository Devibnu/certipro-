<?php

namespace App\Events;

use App\Models\PraPendaftaran;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PraPendaftaranDitolakEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $praPendaftaran;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(PraPendaftaran $praPendaftaran)
    {
        $this->praPendaftaran = $praPendaftaran;
        $this->timestamp = now();
    }
}
