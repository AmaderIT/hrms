<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CreateDepartmentLeaveEncashmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_leave_encashment', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->id();
            $table->uuid('uuid')->default(DB::raw('(UUID())'));
            $table->unsignedInteger('office_division_id')->comment('For show only');
            $table->unsignedInteger('department_id');
            $table->integer('eligible_month');
            $table->year('year');
            $table->double('total_payable_amount',10,2);
            $table->unsignedInteger('prepared_by');
            $table->tinyInteger('divisional_approval_status')->default(0)->comment('0 for pending and 1 for approved');
            $table->unsignedInteger('divisional_approval_by')->nullable();
            $table->string('divisional_remarks',355)->nullable();
            $table->tinyInteger('departmental_approval_status')->default(0)->comment('0 for pending and 1 for approved');
            $table->unsignedInteger('departmental_approval_by')->nullable();
            $table->string('departmental_remarks',355)->nullable();
            $table->tinyInteger('hr_approval_status')->default(0)->comment('0 for pending and 1 for approved');
            $table->unsignedInteger('hr_approval_by')->nullable();
            $table->string('hr_remarks',355)->nullable();
            $table->tinyInteger('accounts_approval_status')->default(0)->comment('0 for pending and 1 for approved');
            $table->unsignedInteger('accounts_approval_by')->nullable();
            $table->string('accounts_remarks',355)->nullable();
            $table->tinyInteger('managerial_approval_status')->default(0)->comment('0 for pending and 1 for approved');
            $table->unsignedInteger('managerial_approval_by')->nullable();
            $table->string('managerial_remarks',355)->nullable();
            $table->tinyInteger('pay_status')->default(0)->comment('0 for unpaid and 1 for paid');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_mode')->default('Bank');
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
        Schema::dropIfExists('department_leave_encashment');
    }
}
