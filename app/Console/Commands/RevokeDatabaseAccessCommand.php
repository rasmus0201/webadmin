<?php

namespace App\Console\Commands;

use App\Services\DatabaseService;
use Illuminate\Console\Command;

class RevokeDatabaseAccessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webadmin:db:revoke-access {username} {database} {--P|--privileges=* : Privileges for user on db. Defaults to "ALL PRIVILEGES"} {--host=localhost : The host for the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke access from a user to a database';

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
            $revokedPrivileges = $databaseService->revokePrivilegesOnDatabase(
                $this->argument('username'),
                $this->option('host'),
                $this->argument('database'),
                !empty($this->option('privileges')) ? $this->option('privileges') : ['ALL PRIVILEGES']
            );

            $privilegesStr = implode(', ', $revokedPrivileges);

            $this->line("Revoked the following privileges: $privilegesStr");
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}
