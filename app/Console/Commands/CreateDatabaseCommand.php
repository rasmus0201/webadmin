<?php

namespace App\Console\Commands;

use App\Services\DatabaseService;
use Illuminate\Console\Command;

class CreateDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webadmin:db:create-database {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new database';

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
            $result = $databaseService->createDatabase(
                $this->argument('name')
            );

            $this->info("Database '{$result['database']}' was created");
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}
