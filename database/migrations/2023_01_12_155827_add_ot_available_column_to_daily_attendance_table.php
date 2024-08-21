<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtAvailableColumnToDailyAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->tinyInteger('is_ot_available')->after('working_min')->comment('0 for false and 1 for true');
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
            $table->dropColumn('is_ot_available');
        });
    }
}
