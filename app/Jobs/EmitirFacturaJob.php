<?php

namespace App\Jobs;

use App\Application\UseCases\EmitirFacturaUseCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EmitirFacturaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    public function __construct(
        public string $origenTipo,
        public int $origenId,
        public int $clientId,
        public float $total,
    ) {}

    public function handle(EmitirFacturaUseCase $uc): void
    {
        $uc->ejecutar($this->origenTipo, $this->origenId, $this->clientId, $this->total);
    }
}
