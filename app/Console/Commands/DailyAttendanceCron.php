<?php

namespace App\Console\Commands;

use App\Http\Controllers\DailyAttendanceController;
use App\Mail\DailyAttendanceSchedulerFailMail;
use App\Models\DailyCronLog;
use App\Models\ZKTeco\DailyAttendance;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DailyAttendanceCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily-attendance:cron {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the Daily Attendance for the last day according to the attendance data for BYSL.';

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
        Log::info("User Late CRON has been fired");
        $date = $this->argument('date');
        $present_dates=[];
        if(!$date){
            $date = date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d'))));
        }
        $check_from_date = date('Y-m-d', strtotime('-6 day', strtotime($date)));
        $attendance_log = DailyCronLog::where('date','>=',$check_from_date)->where('date','<=',$date)->where('cron_key','=','daily_attendance')->get();
        foreach ($attendance_log as $attr_date)
        {
            $present_dates[]=$attr_date->date;
        }
        $begin = new DateTime($check_from_date);
        $end   = new DateTime($date);
        $dailyAttendance = new DailyAttendanceController();
        for($i = $begin; $i <= $end; $i->modify('+1 day')){
            $this_date = $i->format("Y-m-d");
            if(!in_array($this_date,$present_dates)){
                dump($this_date);
                $response = $dailyAttendance->generate($this_date);
                $info = "Daily Attendance CRON (daily-attendance:cron) has been run successfully for the day: " . $this_date;
                if($response["success"] == false) {
                    $data = [
                        'title' => 'Error: Daily Attendance Scheduler unable to run scheduled CRON job',
                        'body'  => "Issue: " . $response["message"]
                    ];
                    Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new DailyAttendanceSchedulerFailMail($data));
                }
                Log::info($info);
                $this->info($info);
            }
        }
    }
}
