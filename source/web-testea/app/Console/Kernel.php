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
        Commands\GradeUpdate::class,
        Commands\TransferReset::class,
        Commands\ExceedingDataDelete::class,
        Commands\YearInitialDataInsert::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 生徒学年更新 毎年3月1日午前0時に実行される
        $schedule->command('command:gradeUpdate')->yearlyOn(3, 1, '00:00');

        // 振替残数リセット 毎年3月1日午前0時10分に実行される
        $schedule->command('command:transferReset')->yearlyOn(3, 1, '00:10');

        // 保持期限超過データ削除 毎年3月1日午前0時20分に実行される
        $schedule->command('command:exceedingDataDelete')->yearlyOn(3, 1, '00:20');

        // 年次初期レコード作成 毎年3月1日午前0時40分に実行される
        $schedule->command('command:yearInitialDataInsert')->yearlyOn(3, 1, '00:40');
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
