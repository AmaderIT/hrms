<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHoldStatusOnLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            DB::statement("ALTER TABLE `loans` CHANGE `status` `status` ENUM('Active','Paid','Pending','Reject', 'Hold') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
            $table->text("hold_remarks")->nullable()->after('hr_approval_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            DB::statement("ALTER TABLE `loans` CHANGE `status` `status` ENUM('Active','Paid','Pending','Reject') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
            $table->dropColumn("hold_remarks");
        });
    }
}
