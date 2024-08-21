<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAndRenameColumnsToEmployeeLeaveEncashmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_leave_encashment', function (Blueprint $table) {
            $table->dropColumn('earned_leave_amount');
            $table->dropColumn('consumed_leave_amount');
            $table->dropColumn('leave_balance');
            $table->renameColumn('payable_amount', 'total_payable_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_leave_encashment', function (Blueprint $table) {
            $table->double('earned_leave_amount',5,2);
            $table->double('consumed_leave_amount',5,2);
            $table->double('leave_balance',5,2);
            $table->renameColumn('total_payable_amount', 'payable_amount');
        });
    }
}
