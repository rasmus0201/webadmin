<?php

namespace App\Console\Commands;

use App\Services\DatabaseService;
use Illuminate\Console\Command;

class DeleteDatabaseUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webadmin:db:delete-user {username} {--host=localhost : The host for the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a database user';

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
            $result = $databaseService->deleteUser(
                $this->argument('username'),
                $this->option('host')
            );

            $this->info("User '{$result['username']}@{$result['host']}' was deleted");
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}
