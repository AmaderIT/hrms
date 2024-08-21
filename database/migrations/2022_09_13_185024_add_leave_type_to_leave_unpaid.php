<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaveTypeToLeaveUnpaid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_unpaid', function (Blueprint $table) {
            $table->tinyInteger('is_half_day')->comment('0=Full day leave, 1=Half day leave')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_unpaid', function (Blueprint $table) {
            $table->dropColumn('is_half_day');
        });
    }
}
