<?php

namespace App\Console\Commands;

use App\Http\Controllers\LeaveUnpaidController;
use App\Http\Controllers\OverTimeController;
use App\Mail\LeaveUnpaidSchedulerFailMail;
use App\Mail\OverTimeSchedulerFailMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OverTimeCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'over-time:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the eligible overtime for the last day according to the attendance data for the company.';

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
        Log::info("Over Time CRON has been fired");

        $leaveUnpaid = new OverTimeController();
        $response = $leaveUnpaid->generate();

        $info = "Over Time CRON (over-time:cron) has been run successfully for the day: " . date('M d, Y', strtotime("yesterday"));

        if($response["success"] == false) {
            $data = [
                'title' => 'Error: Over Time Scheduler unable to run scheduled CRON job',
                'body'  => "Issue: " . $response["message"]
            ];

            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new OverTimeSchedulerFailMail($data));
        }

        Log::info($info);

        $this->info($info);
    }
}
