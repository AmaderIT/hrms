<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNullableColumnsToDailyAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->double('present_count',10,2)->default(1);
            $table->boolean('is_late_final');
            $table->double('late_min_final',10,2);
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
            $table->dropColumn('present_count');
            $table->dropColumn('is_late_final');
            $table->dropColumn('late_min_final');
        });
    }
}
