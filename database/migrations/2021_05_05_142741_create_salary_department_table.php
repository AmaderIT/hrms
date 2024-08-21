<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CreateSalaryDepartmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_department', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->default(Uuid::uuid4()->toString());
            $table->foreignId("office_division_id")->references("id")->on("office_divisions")->cascadeOnDelete();
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->integer("month");
            $table->year("year");
            $table->enum("status", [0, 1])->default(0);
            $table->double("total_payable_amount", 10, 2);
            $table->foreignId("prepared_by")->nullable()->references("id")->on("users")->cascadeOnDelete();

            $table->tinyInteger("divisional_approval_status")->default(\App\Models\SalaryDepartment::STATUS_PENDING);
            $table->foreignId("divisional_approval_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->text("divisional_remarks");

            $table->tinyInteger("departmental_approval_status")->default(\App\Models\SalaryDepartment::STATUS_PENDING);
            $table->foreignId("departmental_approval_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->text("departmental_remarks");

            $table->tinyInteger("hr_approval_status")->default(\App\Models\SalaryDepartment::STATUS_PENDING);
            $table->foreignId("hr_approval_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->text("hr_remarks");

            $table->tinyInteger("accounts_approval_status")->default(\App\Models\SalaryDepartment::STATUS_PENDING);
            $table->foreignId("accounts_approval_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->text("accounts_remarks");

            $table->tinyInteger("managerial_approval_status")->default(\App\Models\SalaryDepartment::STATUS_PENDING);
            $table->foreignId("managerial_approval_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->text("managerial_remarks");

            $table->date("paid_at")->nullable();
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
        Schema::dropIfExists('salary_department');
    }
}
