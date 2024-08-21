<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewRemarksColumnOnLeaveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->tinyInteger('is_reapply')->default(0)->comment('1 when an application is canceled and reapply tag for application');
            $table->longText('remarks')->nullable();
            $table->tinyInteger('half_day_slot')->default(0)->comment('1 is for 1st portion of half day and 2 is 2nd portion of half day');
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
            $table->dropColumn('is_reapply');
            $table->dropColumn('remarks');
            $table->dropColumn('half_day_slot');
        });
    }
}
