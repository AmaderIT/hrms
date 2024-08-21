<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid("salary_department_uuid");
            $table->string('action')->nullable()->comment('Salary Generated, Department Approved, Department Rejected, Division Approved, Division Rejected, HR Approved, HR Rejected, Accounts Approved, Accounts Rejected, Management Approved, Management Rejected, Salary Paid');
            $table->string('remark')->nullable();
            $table->foreignId("action_taken_by")->references("id")->on("users")->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_logs');
    }
}
