<?php

namespace App\Websites\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateLetsencryptCertificateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'letsencrypt:create {domain} {--email= : Email for registering with external services}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create SSL Certifcate from Letsencrypt';

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
        $domain = $this->argument('domain');

        if (empty($domain)) {
            throw new \RuntimeException("The argument 'domain' is required.");
        }

        $email = $this->option('email');

        if (empty($email)) {
            throw new \RuntimeException("The option '--email' is required.");
        }

        $safeDomain = escapeshellarg($domain);
        $safeEmail = escapeshellarg($email);
        $bin = escapeshellarg(base_path('bin/certbot_manager'));
        $configFile = escapeshellarg(base_path('digitalocean.ini'));

        // First delete any current certifcate
        $this->call('letsencrypt:delete', ['user' => $domain]);

        // Then create new certifcate
        $lastLine = exec(
            sprintf('bash %s certonly --dns-digitalocean --dns-digitalocean-credentials %s -m %s -d %s -d www.%s 2>&1', $bin, $configFile, $safeEmail, $safeDomain, $safeDomain),
            $retArr,
            $retVal
        );

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("certbot failed: '$lastLine'");
        }
    }
}
