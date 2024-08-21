<?php

namespace App\Console;

use App\Console\Commands\DailyAttendanceCron;
use App\Console\Commands\DailyCronChecker;
use App\Console\Commands\DailyWorkSlotLogEntryForEveryUser;
use App\Console\Commands\EmployeePermanentCron;
use App\Console\Commands\HolidayAllowanceCron;
use App\Console\Commands\LeaveUnpaidCron;
use App\Console\Commands\MigrationEmploymentStatus;
use App\Console\Commands\OverTimeCron;
use App\Console\Commands\SendChallanToWarehouse;
use App\Console\Commands\UserLateCron;
use App\Mail\CronSchedulerFailureMail;
use App\Mail\DailyAttendanceSchedulerFailMail;
use App\Mail\HolidayAllowanceSchedulerFailMail;
use App\Mail\LeaveUnpaidSchedulerFailMail;
use App\Mail\OverTimeSchedulerFailMail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        LeaveUnpaidCron::class,
        OverTimeCron::class,
        HolidayAllowanceCron::class,
        // UserMealCron::class,
        UserLateCron::class,
        DailyAttendanceCron::class,
        EmployeePermanentCron::class,
        MigrationEmploymentStatus::class,
        DailyCronChecker::class,
        DailyWorkSlotLogEntryForEveryUser::class,
        SendChallanToWarehouse::class

    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        Log::channel('scheduler')->info('I am running at '.now()->toDateTimeString());
        // $schedule->command('inspire')->hourly();

        # Leave Unpaid
        $schedule->command("leave-unpaid:cron")->dailyAt("01:00")->onFailure(function () {
            $data = [
                'title' => 'Error: Leave UnPaid Scheduler unable to run scheduled CRON job',
                'body' => "Scheduler crashed during running on the server."
            ];

            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new LeaveUnpaidSchedulerFailMail($data));
        });

        # Overtime Allowance
        $schedule->command("over-time:cron")->dailyAt("07:00")->onFailure(function () {
            $data = [
                'title' => 'Error: Over Time Scheduler unable to run scheduled CRON job',
                'body' => "Scheduler crashed during running on the server."
            ];

            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new OverTimeSchedulerFailMail($data));
        });

        # Holiday Allowance
        $schedule->command("holiday-allowance:cron")->dailyAt("08:00")->onFailure(function () {
            $data = [
                'title' => 'Error: Holiday Allowance Scheduler unable to run scheduled CRON job',
                'body' => "Scheduler crashed during running on the server."
            ];

            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new HolidayAllowanceSchedulerFailMail($data));
        });

        # User Meal
        /*$schedule->command("user-meal:cron")->dailyAt("10:00")->onFailure(function () {
            $data = [
                'title' => 'Error: User Meal Scheduler unable to run scheduled CRON job',
                'body'  => "Scheduler crashed during running on the server."
            ];

            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new UserMealSchedulerFailMail($data));
        });*/

        # User Late
        $schedule->command("user-late:cron")->dailyAt("02:00")->onFailure(function () {
            $data = [
                'title' => 'Error: User Late Scheduler unable to run scheduled CRON job',
                'body' => "Scheduler crashed during running on the server."
            ];

            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new HolidayAllowanceSchedulerFailMail($data));
        });

        # Daily Attendance
        $schedule->command("daily-attendance:cron")->dailyAt("04:00")->onFailure(function () {
            $data = [
                'title' => 'Error: Daily Attendance Scheduler unable to run scheduled CRON job',
                'body' => "Scheduler crashed during running on the server."
            ];

            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new DailyAttendanceSchedulerFailMail($data));
        });

        # Item adjustment from whms
        $schedule->command("replication:item")->dailyAt("01:00")->onFailure(function () {
            $data = [
                'title' => 'Error: Daily Item Replication from WHMS Scheduler unable to run scheduled CRON job',
                'body' => "Scheduler crashed during running on the server."
            ];

            Mail::to("mohammed.yasir@byslglobal.com")->cc(["sahedul.hasan@byslglobal.com"])->send(new DailyAttendanceSchedulerFailMail($data));
        });

        # send challan to WHMS
        $schedule->command("command:send-challan-to-warehouse")
            ->everyMinute()
            ->unlessBetween('00:55', '01:06')
            ->unlessBetween('01:55', '02:06')
            ->unlessBetween('03:55', '04:06')
            ->unlessBetween('07:55', '08:06');

        # Employee Permanent
        $schedule->command("daily-provision-ending-check:cron")->dailyAt("04:00");

        # Work slot log entry for every user
        $schedule->command("daily-work-slot-log-entry:cron")->twiceDailyAt(6, 12, 00);

        $schedule->command("daily-cron-checker:cron")->twiceDailyAt(8, 9, 00);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
