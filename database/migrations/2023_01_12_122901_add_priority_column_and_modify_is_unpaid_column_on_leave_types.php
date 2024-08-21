<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPriorityColumnAndModifyIsUnpaidColumnOnLeaveTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            DB::statement("ALTER TABLE `leave_types` CHANGE COLUMN `is_unpaid` `is_paid` tinyint NOT NULL DEFAULT 1 COMMENT '0 for unpaid  and 1 for paid and 2 for encashment paid' AFTER `name`");
            $table->smallInteger('priority')->after('is_paid')->comment('Ordering no set')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
}
