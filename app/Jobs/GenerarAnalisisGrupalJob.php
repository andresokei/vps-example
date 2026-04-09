<?php

namespace App\Jobs;

use App\Services\AnalisisGrupalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerarAnalisisGrupalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        public readonly int $grupoId,
        public readonly int $asignacionTestId,
        public readonly bool $forceRefresh = false,
    ) {}

    public function handle(AnalisisGrupalService $service): void
    {
        $service->generar($this->grupoId, $this->asignacionTestId, $this->forceRefresh);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerarAnalisisGrupalJob falló', [
            'grupo_id' => $this->grupoId,
            'asignacion_test_id' => $this->asignacionTestId,
            'error' => $e->getMessage(),
        ]);
    }
}
