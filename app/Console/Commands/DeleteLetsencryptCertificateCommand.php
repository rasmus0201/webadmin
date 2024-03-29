<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteLetsencryptCertificateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'letsencrypt:delete {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete SSL Certifcate from Letsencrypt';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bin = base_path('bin/certbot_manager');

        // Delete certifcate
        $lastLine = exec(
            escapeshellcmd(sprintf(
                '%s delete --cert-name %s',
                $bin,
                escapeshellarg($this->argument('domain'))
            )) . ' 2>&1',
            $retArr,
            $retVal
        );

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("certbot failed: '$lastLine'");
        }
    }
}
