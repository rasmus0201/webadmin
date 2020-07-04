<?php

namespace App\Console\Commands;

use App\Helpers\DatabaseSluggifier;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Support\Str;

class GrantDatabaseAccessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:grant-access {username} {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant access to a user to a database';

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
    public function handle(DB $db)
    {
        $username = DatabaseSluggifier::username($this->argument('username'));
        $database = DatabaseSluggifier::database($this->argument('database'));

        $ret1 = $db->connection('webadmin')->statement(
            "GRANT ALL PRIVILEGES ON $database.* TO '$username'@'localhost'"
        );

        $ret2 = $db->connection('webadmin')->statement("FLUSH PRIVILEGES");

        if (!$ret1 || !$ret2) {
            throw new \Exception('Something went wrong on database user creation');
        }
    }
}
