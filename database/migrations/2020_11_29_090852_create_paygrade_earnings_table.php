<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PayGradeEarning;

class CreatePaygradeEarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paygrade_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId("pay_grade_id")->references("id")->on("pay_grades")->cascadeOnDelete();
            $table->foreignId("earning_id")->references("id")->on("earnings")->cascadeOnDelete();
            $table->enum('type', [PayGradeEarning::TYPE_PERCENTAGE, PayGradeEarning::TYPE_FIXED, PayGradeEarning::TYPE_REMAINING]);
            $table->integer('value')->default(0);
            $table->integer('tax_exempted')->default(0);
            $table->integer('tax_exempted_percentage')->default(0);
            $table->boolean('non_taxable');
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
        Schema::dropIfExists('paygrade_allowances');
    }
}
