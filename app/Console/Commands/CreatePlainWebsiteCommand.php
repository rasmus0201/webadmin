<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreatePlainWebsiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webadmin:website:plain {domain} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new "plain"/empty website with Domain, SSL and database setup';

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
        $dbUsername = $this->argument('domain');
        $dbName = $this->argument('domain');
        $dbPassword = Str::random(16);

        $this->call('webadmin:db:create-database', [
            'name' => $dbName,
        ]);

        $this->call('webadmin:db:create-user', [
            'username' => $dbUsername,
            'password' => $dbPassword
        ]);

        $this->call('webadmin:db:grant-access', [
            'username' => $dbUsername,
            'database' => $dbName
        ]);

        $env = [
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => $dbName,
            'DB_USERNAME' => $dbUsername,
            'DB_PASSWORD' => $dbPassword
        ];

        $this->call('webadmin:website:make', [
            '--secure' => true,
            '--email' => $this->argument('email'),
            '--template' => storage_path('app/templates/NewSecureWebsite.tmpl'),
            '--domain' => $this->argument('domain'),
            '--env' => http_build_query($env, '', "\n") . PHP_EOL
        ]);
    }
}
