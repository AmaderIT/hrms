<?php

namespace App\Console\Commands;

use App\Http\Controllers\WorkSlotEntryForEveryUser;
use App\Mail\CronSchedulerFailureMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DailyWorkSlotLogEntryForEveryUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily-work-slot-log-entry:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For every user work slot log entry process twice in a day';

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
        Log::info("Daily work slot entry CRON has been fired");
        $date = date('Y-m-d');
        $workSlotEntry = new WorkSlotEntryForEveryUser();
        dump($date);
        $response = $workSlotEntry->insert($date);
        $info = "Daily work slot log entry CRON (daily-work-slot-log-entry:cron) has been run successfully for the day: " . $date;
        if($response["success"] == false) {
            $data = [
                'title' => 'Error: Daily Work Slot Log Entry Scheduler unable to run scheduled CRON job',
                'body'  => "Issue: " . $response["message"]
            ];
            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new CronSchedulerFailureMail($data));
        }
        Log::info($info);
        $this->info($info);
    }
}
