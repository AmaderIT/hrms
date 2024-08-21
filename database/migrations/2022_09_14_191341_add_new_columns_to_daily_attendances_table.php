<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToDailyAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->boolean('holiday_count')->default(0);
            $table->double('absent_count',10,2)->default(0);
            $table->double('leave_count',10,2)->default(0);
            $table->boolean('is_public_holiday');
            $table->boolean('is_weekly_holiday');
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
            $table->dropColumn('holiday_count');
            $table->dropColumn('absent_count');
            $table->dropColumn('leave_count');
            $table->dropColumn('is_public_holiday');
            $table->dropColumn('is_weekly_holiday');
        });
    }
}
