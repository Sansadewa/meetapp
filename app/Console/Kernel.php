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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        //konfirm run scchedule
        $schedule->call(function () {
            $ch = curl_init(); 
            // set url 
            // http://bpskalsel.com/wabot/api/wa?to=085791927509&message=HaiLagi
            $nohp = '082113767398';
            $message = 'Scheduler has run.';
            curl_setopt($ch, CURLOPT_URL, "http://bpskalsel.com/wabot/api/wa");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "to=".$nohp."&message=".$message);
            $output = curl_exec($ch); 
            curl_close($ch);
        });


        // $schedule->call('App\Http\Controllers\meetController@reminderRuangRapat'); // kirim reminder ke umum

        //reminder harian
        // $schedule->call('App\Http\Controllers\meetController@reminderHostZoom')->dailyAt('06:00'); // kirim remainder host zoom meeting
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
