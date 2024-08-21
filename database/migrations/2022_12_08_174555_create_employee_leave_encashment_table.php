<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CreateEmployeeLeaveEncashmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_leave_encashment', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->id();
            $table->unsignedInteger('department_leave_encashment_id');
            $table->unsignedInteger('user_id');
            $table->string('designation_name',255)->comment('For historical use only');
            $table->double('earned_leave_amount',5,2);
            $table->double('consumed_leave_amount',5,2);
            $table->double('leave_balance',5,2);
            $table->double('basic_salary_amount',10,2);
            $table->json('earning_amounts')->nullable();
            $table->double('gross_salary_amount',10,2);
            $table->double('per_day_salary_amount',10,2);
            $table->double('payable_amount',10,2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_leave_encashment');
    }
}
