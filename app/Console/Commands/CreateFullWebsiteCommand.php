<?php

namespace App\Console\Commands;

use App\Databases\Commands\CreateDatabaseUserCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateFullWebsiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webadmin:new-plain {domain} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new plain website with vhost and database';

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
        $dbName = Str::slug($this->argument('domain'), '_');
        $dbUsername = Str::limit(
            Str::slug($this->argument('domain'), '_'),
            CreateDatabaseUserCommand::USERNAME_LIMIT
        );
        $dbPassword = Str::random(16);

        $this->call('db:create-database', [
            'name' => $dbName,
        ]);

        $this->call('db:create-user', [
            'username' => $dbUsername,
            'password' => $dbPassword
        ]);

        $this->call('db:grant-access', [
            'username' => $dbUsername,
            'database' => $dbName
        ]);

        $this->call('website:make', [
            '--secure' => true,
            '--email' => $this->argument('email'),
            '--template' => app_path('Websites/Nginx/Templates/NewSecureWebsite.stub'),
            '--domain' => $this->argument('domain')
        ]);
    }
}
