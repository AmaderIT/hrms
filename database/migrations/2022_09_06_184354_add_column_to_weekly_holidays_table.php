<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToWeeklyHolidaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weekly_holidays', function (Blueprint $table) {
            $table->date('effective_date')->after('days');
            $table->date('end_date')->after('effective_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('weekly_holidays', function (Blueprint $table) {
            $table->dropColumn('effective_date');
            $table->dropColumn('end_date');
        });
    }
}
