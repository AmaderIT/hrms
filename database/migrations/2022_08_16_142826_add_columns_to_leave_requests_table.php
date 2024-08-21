<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToLeaveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('leave_start_time')->nullable()->comment('For half day leave application')->after('to_date');
            $table->string('leave_end_time')->nullable()->comment('For half day leave application')->after('leave_start_time');
            $table->double('number_of_paid_days',8,2)->nullable()->after('number_of_days');
            $table->double('number_of_unpaid_days',8,2)->nullable()->after('number_of_days');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('leave_start_time');
            $table->dropColumn('leave_end_time');
            $table->dropColumn('number_of_paid_days');
            $table->dropColumn('number_of_unpaid_days');
        });
    }
}
