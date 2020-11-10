<?php

namespace App\Console\Commands;

use App\Builders\Nginx;
use App\Contracts\WebserverContract;
use Illuminate\Console\Command;

class CreateWebsiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webadmin:website:make
                        {--server=nginx}
                        {--secure : Whether or not too generate SSL certifcate & config}
                        {--email= : Email for registering with external services}
                        {--domain= : The domain of the website}
                        {--template= : Template to the server}
                        {--git-repository= : From which git repository to install from}
                        {--git-branch=master: The branch to checkout}
                        {--env= : The .env file contents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make new website';

    /**
     * Servers with their class
     *
     * @var array
     */
    private $servers = [
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
        $server = $this->option('server');

        if (!isset($this->servers[$server])) {
            throw new \RuntimeException("Server [$server] not supported.");
        }

        $domain = $this->option('domain');
        $template = $this->option('template');
        $email = $this->option('email');

        if (empty($domain) || empty($template) || empty($email)) {
            throw new \RuntimeException("Both --domain, --template and --email are required.");
        }

        /** @var \App\Contracts\WebserverContract **/
        $webserver = new $this->servers[$server]();

        // Before doing anything, make sure the webserver
        // is functioning normally
        if (!$webserver->test()) {
            throw new \Exception("There is something wrong with the webserver config.");
        }

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
            $this->call('webadmin:letsencrypt:create', [
                'domain' => $domain,
                'email' => $email
            ]);
        }

        // Test if config is correct setup,
        // if not revert changes (aka delete config file) and exit
        if (!$webserver->test()) {
            $webserver->deleteVirtualHost($vHostConfigFileName, $vHost);

            throw new \Exception("There was something wrong with the specified config.");
        }

        // Install website from git repo or just add default html file
        if ($repository = $this->option('git-repository')) {
            $branch = $this->option('git-branch');
            $rootPath = $vHost->getRootPath();

            if (!file_exists($rootPath)) {
                $r = mkdir($rootPath, 0775, true);
            }

            $idFilePath = '/home/www-data/.ssh/id_rsa';

            exec(
                escapeshellcmd(sprintf(
                    "cd %s && /usr/bin/git -c core.sshCommand=\"ssh -i %s\" clone --recurse-submodules %s . --quiet && git checkout %s --quiet",
                    escapeshellarg($rootPath),
                    escapeshellarg($idFilePath),
                    escapeshellarg($repository),
                    escapeshellarg($branch),
                ))
            );

            if (file_exists($rootPath . '/composer.json')) {
                exec(
                    escapeshellcmd(sprintf(
                        "cd %s && /usr/local/bin/composer install --quiet --no-interaction",
                        escapeshellarg($rootPath)
                    ))
                );
            }

            if (file_exists($rootPath . '/.env.example')) {
                exec(
                    escapeshellcmd(sprintf(
                        "cd %s && cp .env.example .env",
                        escapeshellarg($rootPath)
                    ))
                );
            }
        } else {
            $publicPath = $vHost->getPublicPath();
            $indexPath = $publicPath . '/index.php';

            if (!file_exists($publicPath)) {
                $r = mkdir($publicPath, 0775, true);
            }

            if (!file_exists($indexPath)) {
                $h = fopen($indexPath, 'w');
                fwrite($h, '<h1>This website is waiting for configuration</h1>');
                fclose($h);
            }
        }

        // Publish any .env file with contents
        if ($env = $this->option('env')) {
            $envFile = $vHost->getRootPath() . '/.env';

            $h = fopen($envFile, 'w');
            fwrite($h, $env);
            fclose($h);
        }

        $webserver->reload();
    }
}
