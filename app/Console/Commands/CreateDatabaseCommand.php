<?php

namespace App\Console\Commands;

use App\Helpers\DatabaseSluggifier;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Support\Str;

class CreateDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create-database {name}';

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
    public function handle(DB $db)
    {
        $name = DatabaseSluggifier::database($this->argument('name'));

        $ret = $db->connection('webadmin')->statement(
            "CREATE DATABASE `$name`"
        );

        if (!$ret) {
            throw new \Exception('Something went wrong on database creation');
        }
    }
}
