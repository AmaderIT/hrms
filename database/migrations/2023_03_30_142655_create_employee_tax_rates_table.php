<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeTaxRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('emp_code');
            $table->integer("month");
            $table->year("year");
            $table->double('maximum_tax_rate',10,2);
            $table->double('tax_amount',10,2)->nullable();
            $table->unsignedInteger('created_by');
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
        Schema::dropIfExists('employee_tax_rates');
    }
}
