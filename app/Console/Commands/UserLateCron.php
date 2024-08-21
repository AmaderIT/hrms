<?php

namespace App\Console\Commands;

use App\Http\Controllers\UserLateController;
use App\Mail\UserLateSchedulerFailMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserLateCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-late:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the User Late Count for the last day according to the attendance data for BYSL.';

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
        Log::info("User Late CRON has been fired");

        $userLate = new UserLateController();
        $response = $userLate->generate();

        $info = "User Late CRON (user-late:cron) has been run successfully for the day: " . date('M d, Y', strtotime("yesterday"));

        if($response["success"] == false) {
            $data = [
                'title' => 'Error: User Late Scheduler unable to run scheduled CRON job',
                'body'  => "Issue: " . $response["message"]
            ];

            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new UserLateSchedulerFailMail($data));
        }

        Log::info($info);

        $this->info($info);
    }
}
