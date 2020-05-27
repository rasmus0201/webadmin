<?php

namespace App\Websites\Commands;

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
        $safeDomain = escapeshellarg($this->argument('domain'));
        $bin = escapeshellarg(base_path('bin/certbot_manager'));

        // Delete certifcate
        $lastLine = exec(sprintf('%s delete --cert-name %s 2>&1', $bin, $safeDomain), $retArr, $retVal);

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("certbot failed: '$lastLine'");
        }
    }
}
