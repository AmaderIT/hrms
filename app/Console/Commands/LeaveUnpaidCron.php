<?php

namespace App\Console\Commands;

use App\Models\DailyCronLog;
use DB;
use App\Models\LeaveUnpaid;
use App\Http\Controllers\LeaveUnpaidController;
use App\Mail\LeaveUnpaidSchedulerFailMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeaveUnpaidCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave-unpaid:cron {date?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the unpaid leaves for the last day according to the attendance data for the company.';

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
        $date = $this->argument('date');
        Log::info("Leave Unpaid CRON has been fired");

        $leaveUnpaid = new LeaveUnpaidController();

        if ($date) {
            if (count($date)> 1) {
                $commandDates = getDatesFromRange($date[0], $date[1], $format = 'Y-m-d');

                foreach ($commandDates as $k => $yesterdayDate) {
                    $response = $leaveUnpaid->generateLeaveUnpaidReportForYesterday($yesterdayDate);

                    if($response["success"] == true) {
                        DB::beginTransaction();
                        LeaveUnpaid::insert($response['data']);
                        DailyCronLog::insert(['cron_key'=>'leave_unpaid','date'=>$yesterdayDate,'created_at'=>now()]);
                        DB::commit();

                        session()->flash('message', 'UnPaid Leave Generated Successfully');
                        $info = "Leave Unpaid CRON (leave-unpaid:cron) has been run successfully for the day: " .date('M d, Y', strtotime($yesterdayDate));
                    } elseif($response["success"] == false) {
                        session()->flash("type", "error");
                        session()->flash('message', 'Sorry! Something happened wrong!!');
                        $info = "Leave Unpaid CRON (leave-unpaid:cron) failed for the day: " .date('M d, Y', strtotime($yesterdayDate));
                    }
                }
            } else {
                $date = $date[0];
                $response = $leaveUnpaid->generateLeaveUnpaidReportForYesterday($date);

                if($response["success"] == true) {
                    DB::beginTransaction();
                    LeaveUnpaid::insert($response['data']);
                    DailyCronLog::insert(['cron_key'=>'leave_unpaid','date'=>$date,'created_at'=>now()]);
                    DB::commit();

                    $info = "Leave Unpaid CRON (leave-unpaid:cron) has been run successfully for the day: " .date('M d, Y', strtotime($date));
                } elseif($response["success"] == false) {
                    $info = "Leave Unpaid CRON (leave-unpaid:cron) faild for the day: " .date('M d, Y', strtotime($date));

                    $data = [
                        'title' => 'Error: Leave Unpaid Scheduler unable to run scheduled CRON job',
                        'body'  => "Issue: " . $response["message"]
                    ];

                    Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new LeaveUnpaidSchedulerFailMail($data));
                }
            }

        } else {
            $yesterdayDate = date('Y-m-d', strtotime("yesterday"));
            $response = $leaveUnpaid->generateLeaveUnpaidReportForYesterday($yesterdayDate);

            if($response["success"] == true) {
                DB::beginTransaction();
                LeaveUnpaid::insert($response['data']);
                DailyCronLog::insert(['cron_key'=>'leave_unpaid','date'=>$yesterdayDate,'created_at'=>now()]);
                DB::commit();

                $info = "Leave Unpaid CRON (leave-unpaid:cron) has been run successfully for the day: " .date('M d, Y', strtotime($yesterdayDate));
            } elseif($response["success"] == false) {
                $info = "Leave Unpaid CRON (leave-unpaid:cron) faild for the day: " .date('M d, Y', strtotime($yesterdayDate));

                $data = [
                    'title' => 'Error: Leave Unpaid Scheduler unable to run scheduled CRON job',
                    'body'  => "Issue: " . $response["message"]
                ];

                Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new LeaveUnpaidSchedulerFailMail($yesterdayDate));
            }
        }

        Log::info($info);

        $this->info($info);
    }
}
