<?php

namespace App\Console\Commands;

use App\Models\DailyCronLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DailyCronChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily-cron-checker:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check daily important cron ran or not; if not then run it';

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
        Log::info("Cron checker has been fired");
        $date = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d'))));
        $attendance_cron_log = DailyCronLog::where('date','=',$yesterday)->where('cron_key','=','daily_attendance')->first();
        if(!$attendance_cron_log){
            Artisan::call('daily-attendance:cron');
        }
        $leave_unpaid_cron_log = DailyCronLog::where('date','=',$yesterday)->where('cron_key','=','leave_unpaid')->first();
        if(!$leave_unpaid_cron_log){
            Artisan::call('leave-unpaid:cron');
        }
        $daily_work_slot_cron_log = DailyCronLog::where('date','=',$date)->where('cron_key','=','daily_work_slot')->first();
        if(!$daily_work_slot_cron_log){
            Artisan::call('daily-work-slot-log-entry:cron');
        }
    }
}
