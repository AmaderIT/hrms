<?php

use App\Models\PayGrade;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_grades', function (Blueprint $table) {
            $table->id();
            $table->string('name',100)->unique();
            $table->integer("range_start_from")->default(0);
            $table->integer("range_end_to")->default(0);
            $table->integer('percentage_of_basic');
            $table->enum("based_on", [PayGrade::BASED_ON_BASIC, PayGrade::BASED_ON_GROSS]);
            $table->string('overtime_formula', 100)->nullable();
            $table->string('holiday_allowance_formula', 100)->nullable();
            $table->string('weekend_allowance_formula', 100)->nullable();
            $table->foreignId("tax_id")->nullable()->references("id")->on("taxes")->cascadeOnDelete();
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
        Schema::dropIfExists('pay_grades');
    }
}
