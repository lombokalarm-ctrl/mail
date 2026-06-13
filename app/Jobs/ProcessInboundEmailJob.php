<?php

namespace App\Jobs;

use App\Services\InboundEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessInboundEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $rawEmail) {}

    /**
     * Execute the job.
     */
    public function handle(InboundEmailService $service): void
    {
        $service->ingest($this->rawEmail);
    }
}
