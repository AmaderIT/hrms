<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->references("id")->on("users");
            $table->string("emp_code");
            $table->dateTime("time_in");
            $table->dateTime("time_out");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->dropColumn('roaster_start_time');
            $table->dropColumn('roaster_end_time');
            $table->dropColumn('late_count_time');
            $table->dropColumn('is_late_day');
            $table->dropColumn('late_in_min');
            $table->dropColumn('working_min');
            $table->dropColumn('overtime_min');
        });
    }
}
