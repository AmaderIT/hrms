<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddEnumValueForCronKeyColumnToDailyCronLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE daily_cron_log MODIFY cron_key ENUM('daily_attendance','daily_work_slot') DEFAULT 'daily_attendance'");
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE daily_cron_log MODIFY cron_key ENUM('daily_attendance') DEFAULT 'daily_attendance'");
    }
}
