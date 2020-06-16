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
        \App\Databases\Commands\CreateDatabaseCommand::class,
        \App\Databases\Commands\CreateDatabaseUserCommand::class,
        \App\Databases\Commands\GrantDatabaseAccessCommand::class,
        \App\Websites\Commands\CreateWebsiteCommand::class,
        \App\Websites\Commands\CreateLetsencryptCertificateCommand::class,
        \App\Websites\Commands\DeleteLetsencryptCertificateCommand::class,
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
