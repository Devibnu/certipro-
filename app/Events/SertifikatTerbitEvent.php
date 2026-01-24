<?php

namespace App\Events;

use App\Models\Sertifikat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SertifikatTerbitEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sertifikat;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(Sertifikat $sertifikat)
    {
        $this->sertifikat = $sertifikat;
        $this->timestamp = now();
    }
}
