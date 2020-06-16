<?php

namespace App\Databases\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Support\Str;

class CreateDatabaseUserCommand extends Command
{
    const USERNAME_LIMIT = 24;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create-user {username} {password}';

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
    public function handle(DB $db)
    {
        $username = Str::limit(Str::slug($this->argument('username'), '_'), self::USERNAME_LIMIT);
        $password = $this->argument('password');

        $ret = $db->connection('webadmin')->statement(
            "CREATE USER '$username'@'localhost' IDENTIFIED BY '$password'"
        );

        if (!$ret) {
            throw new \Exception('Something went wrong on database user creation');
        }
    }
}
