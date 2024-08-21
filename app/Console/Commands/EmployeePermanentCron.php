<?php

namespace App\Console\Commands;

use App\Http\Controllers\DailyAttendanceController;
use App\Http\Controllers\DashboardNotificationController;
use App\Mail\CronSchedulerFailureMail;
use App\Mail\DailyAttendanceSchedulerFailMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmployeePermanentCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily-provision-ending-check:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Employment status will be updated when provision period will expired.';

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
     * @return void
     */
    public function handle()
    {
        Log::info("Employee permanent cron has been fired");

        $obj = new DashboardNotificationController();
        $response = $obj->makeEmployeePermanent();

        $info = "Employee permanent cron (daily-attendance:cron) has been run successfully for this day: " . date('M d, Y', strtotime("yesterday"));

        if ($response["success"] == false) {
            $title = "Error: Employee permanent scheduler unable to run scheduled cron job";
            $data = [
                'subject' => 'Employee Permanent Scheduler Failed',
                'title' => $title,
                'body' => "Issue: " . $response["message"]
            ];


            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new CronSchedulerFailureMail($data));
            $info = $title;
        }

        Log::info($info);
        Log::info($response);

        $this->info($info);
    }
}
