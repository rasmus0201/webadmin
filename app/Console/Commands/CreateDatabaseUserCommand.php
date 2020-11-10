<?php

namespace App\Console\Commands;

use App\Services\DatabaseService;
use Illuminate\Console\Command;

class CreateDatabaseUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webadmin:db:create-user {username} {password} {--host=localhost : The host for the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new database user';

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
    public function handle(DatabaseService $databaseService)
    {
        try {
            $result = $databaseService->createUser(
                $this->argument('username'),
                $this->option('host'),
                $this->argument('password')
            );

            $this->info("User '{$result['username']}@{$result['host']}' was created");
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}
