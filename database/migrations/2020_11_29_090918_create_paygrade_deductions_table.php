<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PayGradeDeduction;

class CreatePaygradeDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paygrade_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId("pay_grade_id")->references("id")->on("pay_grades")->cascadeOnDelete();
            $table->foreignId("deduction_id")->references("id")->on("deductions")->cascadeOnDelete();
            $table->enum("type", array(PayGradeDeduction::TYPE_PERCENTAGE, PayGradeDeduction::TYPE_FIXED));
            $table->integer("value")->default(0);
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
        Schema::dropIfExists('paygrade_deductions');
    }
}
