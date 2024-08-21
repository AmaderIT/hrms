<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CreateSalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->default(Uuid::uuid4()->toString());
            $table->foreignId("user_id")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("office_division_id")->references("id")->on("office_divisions")->cascadeOnDelete();
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->foreignId("designation_id")->references("id")->on("designations")->cascadeOnDelete();
            $table->foreignId("pay_grade_id")->references("id")->on("pay_grades")->cascadeOnDelete();
            $table->foreignId("tax_id")->nullable()->references("id")->on("taxes")->cascadeOnDelete();
            $table->double("gross", 10, 2);
            $table->double("basic", 10, 2);
            $table->double("house_rent", 10, 2);
            $table->double("medical_allowance", 10, 2);
            $table->double("conveyance", 10, 2);
            $table->json("earnings");
            $table->json("cash_earnings");
            $table->double("total_earning", 10, 2);
            $table->double("total_cash_earning", 10, 2);
            $table->double("regular_duty", 8, 2);
            $table->double("weekend_holiday_duty", 8, 2);
            $table->double("official_holiday_duty", 8, 2);
            $table->double("leave_days", 8, 2);
            $table->double("absent_days", 8, 2);
            $table->double("absent_salary_deduction", 10, 2);
            $table->double("overtime_hours", 8, 2);
            $table->double("weekend_holiday_days", 8, 2);
            $table->double("official_holiday_days", 8, 2);
            $table->json("deductions");
            $table->double("total_deduction", 10, 2);
            $table->double("overtime_amount", 10, 2);
            $table->double("holiday_amount", 8, 2);
            $table->double("parcel_charge", 8, 2);
            $table->double("delivery_bonus", 8, 2);
            // $table->double("leave_unpaid_amount", 10, 2);
            $table->double("taxable_amount", 10, 2);
            $table->double("payable_tax_amount", 10, 2);
            $table->double("advance", 10, 2);
            $table->double("casual_leave", 10, 2);
            $table->double("earn_leave", 10, 2);
            $table->double("loan", 10, 2);
            $table->double("late", 10, 2);
            $table->double("remaining_tax_opening_balance", 10, 2);
            $table->double("payable_amount", 10, 2);
            $table->double("net_payable_amount", 10, 2);
            $table->double("attendance_hours", 10, 2);
            $table->json("late_leave_deduction");
            $table->double("late_salary_deduction", 10, 2);
            $table->boolean("status")->default(0);
            $table->enum("month", array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12));
            $table->year("year");
            $table->string("payment_mode", 20);
            $table->text("remarks");
            $table->timestamp("paid_at", 0)->nullable();
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
        Schema::dropIfExists('salaries');
    }
}
