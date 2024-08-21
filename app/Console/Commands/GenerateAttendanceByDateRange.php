<?php

namespace App\Console\Commands;

use App\Http\Controllers\DaterangeAttendanceGenerate;
use App\Mail\DailyAttendanceSchedulerFailMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateAttendanceByDateRange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        Log::info("Daily Attendance Record start using date range");

        $dailyAttendance = new DaterangeAttendanceGenerate();
        $response = $dailyAttendance->generate();

        $info = "Daily Attendance CRON (daily-attendance:cron) has been run successfully for the day: " . date('M d, Y', strtotime("yesterday"));

        if($response["success"] == false) {
            $data = [
                'title' => 'Error: Daily Attendance Scheduler unable to run scheduled CRON job',
                'body'  => "Issue: " . $response["message"]
            ];

            //Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new DailyAttendanceSchedulerFailMail($data));
        }

        Log::info($info);

        $this->info($info);
    }
}
