<?php

namespace App\Websites\Commands;

use App\Websites\Contracts\WebserverContract;
use App\Websites\Nginx\Nginx;
use Illuminate\Console\Command;

class CreateWebsiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:make
                        {--driver=nginx}
                        {--secure : Whether or not too generate SSL certifcate & config}
                        {--email= : Email for registering with external services}
                        {--domain= : The domain of the website}
                        {--template= : Template to the driver}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make new website';

    /**
     * Drivers with their class
     *
     * @var array
     */
    private $drivers = [
        'nginx' => Nginx::class
    ];

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
        $driver = $this->option('driver');

        if (!isset($this->drivers[$driver])) {
            throw new \RuntimeException("Driver [$driver] not supported.");
        }

        $domain = $this->option('domain');
        $template = $this->option('template');
        $email = $this->option('email');

        if (empty($domain) || empty($template) || empty($email)) {
            throw new \RuntimeException("Both --domain, --template and --email is required.");
        }

        $webserver = new $this->drivers[$driver]();

        // Generate virtual host for domain
        $vHostConfigFileName = $webserver->getVirtualHostName($domain);
        $vHost = $webserver->createVirtualHost(
            $template,
            $vHostConfigFileName,
            [
                WebserverContract::DOMAIN => $domain
            ]
        );

        // Genereate SSL certicate & save it
        if ($this->option('secure')) {
            $this->call('letsencrypt:create', [
                'domain' => $domain,
                '--email' => $email
            ]);
        }

        // TODO Test if config is correct setup,
        // if not revert changes (aka delete config file) and exit

        $webserver->createRootDirectory($vHost);

        // TODO Install website from git repo or just add default html file
        // If git we need to support "composer install" if a composer.json is found
        // It also would be nice to make it able to do a specific branch/tag
        // There should probably be a way to access the id_rsa.pub for the www-user,
        // so you can allow it to read in the repository

        $webserver->reload();
    }
}
