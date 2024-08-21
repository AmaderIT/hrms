<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxAmountColumnInEmployeeLeaveEncashmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_leave_encashment', function (Blueprint $table) {
            $table->double('tax_amount',10,2)->after('leave_details');
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
            $table->dropColumn('tax_amount');
        });
    }
}
