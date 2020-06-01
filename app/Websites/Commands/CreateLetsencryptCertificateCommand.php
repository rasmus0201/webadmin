<?php

namespace App\Websites\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        $email = $this->option('email');

        if (empty($email)) {
            throw new \RuntimeException("The option '--email' is unfortunately not optional!");
        }

        $domain = $this->argument('domain');
        $bin = base_path('bin/certbot_manager');
        $configFile = base_path('digitalocean.ini');
        $safeDomain = escapeshellarg($domain);
        $safeEmail = escapeshellarg($email);

        // First delete any current certifcate
        $this->call('letsencrypt:delete', ['domain' => $domain]);

        $cmd = sprintf(
            '%s certonly --dns-digitalocean --dns-digitalocean-credentials %s -m %s -d %s -d www.%s',
            $bin,
            $configFile,
            $safeEmail,
            $safeDomain,
            $safeDomain
        );

        // Check if wildcard certifcate
        if (Str::contains($domain, '*.')) {
            $cmd = sprintf(
                '%s certonly --dns-digitalocean --dns-digitalocean-credentials %s -m %s -d %s',
                $bin,
                $configFile,
                $safeEmail,
                $safeDomain
            );
        }

        // Then create new certifcate
        $lastLine = exec(
            escapeshellcmd($cmd) . ' 2>&1',
            $retArr,
            $retVal
        );

        if ($retVal !== 0) {
            Log::error($retArr);
            throw new \RuntimeException("certbot failed: '$lastLine'");
        }
    }
}
