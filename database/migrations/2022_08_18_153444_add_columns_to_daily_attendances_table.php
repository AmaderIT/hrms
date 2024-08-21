<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToDailyAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->date('date')->after('emp_code');
            $table->string('roaster_start_time')->after('time_out');
            $table->string('roaster_end_time')->after('roaster_start_time');
            $table->string('late_count_time')->after('roaster_end_time')->nullable();
            $table->boolean('is_late_day')->after('late_count_time');
            $table->double('late_in_min',10,2)->after('is_late_day')->comment('multiply 60 with min to get seconds');
            $table->double('working_min',10,2)->after('late_in_min')->comment('multiply 60 with min to get seconds');
            $table->double('overtime_min',10,2)->after('working_min')->nullable()->comment('multiply 60 with min to get seconds');
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
