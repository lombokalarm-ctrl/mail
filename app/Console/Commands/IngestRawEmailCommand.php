<?php

namespace App\Console\Commands;

use App\Jobs\ProcessInboundEmailJob;
use App\Services\InboundEmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class IngestRawEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:ingest
                            {--file= : Path ke file raw email}
                            {--sync : Proses sinkron tanpa queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memproses raw email catch-all dari stdin atau file';

    /**
     * Execute the console command.
     */
    public function handle(InboundEmailService $service): int
    {
        $rawEmail = $this->option('file')
            ? File::get($this->option('file'))
            : stream_get_contents(STDIN);

        if (! is_string($rawEmail) || trim($rawEmail) === '') {
            $this->error('Raw email kosong. Kirim data lewat stdin atau gunakan --file=path.');

            return self::FAILURE;
        }

        if ($this->option('sync')) {
            $email = $service->ingest($rawEmail);
            $this->info("Email tersimpan ke inbox {$email->inbox->inbox_name} dengan ID {$email->id}.");

            return self::SUCCESS;
        }

        ProcessInboundEmailJob::dispatch($rawEmail);
        $this->info('Raw email masuk ke queue untuk diproses.');

        return self::SUCCESS;
    }
}
