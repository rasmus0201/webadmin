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

        if (empty($domain) || empty($template)) {
            throw new \RuntimeException("Both --domain and --template is required.");
        }

        $webserver = new $this->drivers[$driver]();

        $webserver->template($template);
        $webserver->createWebsite([
            WebserverContract::DOMAIN => $domain
        ]);

        $webserver->save($webserver->getWebsiteConfigPath($domain));
        $webserver->reload();
    }
}
