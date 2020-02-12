<?php

namespace Flashpoint\Oxidiser\Console;

use Flashpoint\Oxidiser\Console\Commands\ManageUserCommand;
use Flashpoint\Oxidiser\Console\Commands\MigrateMakeCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class LumenKernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        MigrateMakeCommand::class,
        ManageUserCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
