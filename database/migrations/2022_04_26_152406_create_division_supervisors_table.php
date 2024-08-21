<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDivisionSupervisorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('division_supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId("office_division_id")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("supervised_by")->references("id")->on("users")->cascadeOnDelete();
            $table->date("start_date")->nullable();
            $table->date("end_date")->nullable();
            $table->enum("status", [0, 1])->default(\App\Models\DepartmentSupervisor::STATUS_ACTIVE);
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
        Schema::dropIfExists('division_supervisors');
    }
}
