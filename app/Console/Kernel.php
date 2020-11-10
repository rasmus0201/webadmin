<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CreateDatabaseCommand::class,
        \App\Console\Commands\DeleteDatabaseCommand::class,

        \App\Console\Commands\CreateDatabaseUserCommand::class,
        \App\Console\Commands\DeleteDatabaseUserCommand::class,

        \App\Console\Commands\GrantDatabaseAccessCommand::class,
        \App\Console\Commands\RevokeDatabaseAccessCommand::class,

        \App\Console\Commands\CreateWebsiteCommand::class,
        \App\Console\Commands\CreatePlainWebsiteCommand::class,

        \App\Console\Commands\CreateLetsencryptCertificateCommand::class,
        \App\Console\Commands\DeleteLetsencryptCertificateCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
