<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CreateBonusDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_departments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->default(Uuid::uuid4()->toString());
            $table->foreignId("office_division_id")->references("id")->on("office_divisions")->cascadeOnDelete();
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->foreignId("bonus_id")->references("id")->on("bonuses")->cascadeOnDelete();
            $table->integer("month");
            $table->year("year");
            $table->double("total_payable_amount", 10, 2);
            $table->foreignId("prepared_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp("prepared_date")->nullable();

            $table->tinyInteger("departmental_approval_status")->default(0)->comment('0=Not Approved, 1=Approved, 2=Reject');
            $table->foreignId("departmental_approval_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp("departmental_approved_date")->nullable();
            $table->text("departmental_remarks")->nullable();

            $table->tinyInteger("divisional_approval_status")->default(0)->comment('0=Not Approved, 1=Approved, 2=Reject');
            $table->foreignId("divisional_approval_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp("divisional_approved_date")->nullable();
            $table->text("divisional_remarks")->nullable();

            $table->tinyInteger("hr_approval_status")->default(0)->comment('0=Not Approved, 1=Approved, 2=Reject');
            $table->foreignId("hr_approval_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp("hr_approved_date")->nullable();
            $table->text("hr_remarks")->nullable();

            $table->tinyInteger("accounts_approval_status")->default(0)->comment('0=Not Approved, 1=Approved, 2=Reject');
            $table->foreignId("accounts_approval_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp("accounts_approved_date")->nullable();
            $table->text("accounts_remarks")->nullable();

            $table->tinyInteger("managerial_approval_status")->default(0)->comment('0=Not Approved, 1=Approved, 2=Reject');
            $table->foreignId("managerial_approval_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp("managerial_approved_date")->nullable();
            $table->text("managerial_remarks")->nullable();
            $table->date("paid_at")->nullable();

            $table->enum("status", [0, 1])->default(0);
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
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
        Schema::dropIfExists('bonus_departments');
    }
}
