<?php

namespace App\Console\Commands;

use App\Http\Controllers\HolidayAllowanceController;
use App\Mail\HolidayAllowanceSchedulerFailMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HolidayAllowanceCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holiday-allowance:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the eligible holiday allowance for the last day according to the attendance data for BYSL.';

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
        Log::info("Holiday CRON has been fired");

        $holidayAllowance = new HolidayAllowanceController();
        $response = $holidayAllowance->generate();

        $info = "Holiday Allowance CRON (holiday-allowance:cron) has been run successfully for the day: " . date('M d, Y', strtotime("yesterday"));

        if($response["success"] == false) {
            $data = [
                'title' => 'Error: Holiday Allowance Scheduler unable to run scheduled CRON job',
                'body'  => "Issue: " . $response["message"]
            ];

            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new HolidayAllowanceSchedulerFailMail($data));
        }

        Log::info($info);

        $this->info($info);
    }
}
