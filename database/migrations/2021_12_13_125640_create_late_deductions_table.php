<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLateDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('late_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->enum("type", ["leave", "salary"]);
            $table->integer("total_days");
            $table->integer("deduction_day");
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
        Schema::dropIfExists('late_deductions');
    }
}
