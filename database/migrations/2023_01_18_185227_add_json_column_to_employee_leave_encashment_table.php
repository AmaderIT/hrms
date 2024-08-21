<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJsonColumnToEmployeeLeaveEncashmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_leave_encashment', function (Blueprint $table) {
            $table->json('leave_details')->nullable()->after('per_day_salary_amount');
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
            $table->dropColumn('leave_details');
        });
    }
}
