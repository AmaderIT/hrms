<?php

namespace App\Console\Commands;

use App\Http\Controllers\UserMealController;
use App\Mail\UserMealSchedulerFailMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserMealCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-meal:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This keeps the active meal consumers activity everyday.';

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
        Log::info("Meal Activity CRON has been fired");

        $meal = new UserMealController();
        $response = $meal->insertActiveMealConsumer();

        $info = "Meal Activity CRON (user-meal:cron) has been run successfully for the day: " . date('M d, Y', strtotime("today"));

        if($response["success"] == false) {
            $data = [
                'title' => 'Error: User Meal Scheduler unable to run scheduled CRON job',
                'body'  => "Issue: " . $response["message"]
            ];

            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new UserMealSchedulerFailMail($data));
        }

        Log::info($info);

        $this->info($info);
    }
}
