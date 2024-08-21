<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserLoansTableCommands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_loans', function (Blueprint $table) {
            DB::statement("ALTER TABLE `user_loans` MODIFY COLUMN `status` tinyint(4) NOT NULL DEFAULT 4 COMMENT '4=amount applied, 5=amount approved, 6=amount change applied, 3=amount rejected, 2=deduction pending, 1=deducted/approved'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_loans', function (Blueprint $table) {
            DB::statement("ALTER TABLE `user_loans` MODIFY COLUMN `status` tinyint(4) NOT NULL DEFAULT 4 COMMENT '1=Approved, 2=Pending, 3=Rejected'");
        });
    }
}
