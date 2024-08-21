<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddNewEnumValueForCronKeyColumnToDailyCronLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daily_cron_logs', function (Blueprint $table) {
            DB::statement("ALTER TABLE daily_cron_log MODIFY cron_key ENUM('daily_attendance','daily_work_slot','leave_unpaid') DEFAULT 'daily_attendance'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_cron_logs', function (Blueprint $table) {
            DB::statement("ALTER TABLE daily_cron_log MODIFY cron_key ENUM('daily_attendance','daily_work_slot') DEFAULT 'daily_attendance'");
        });
    }
}
