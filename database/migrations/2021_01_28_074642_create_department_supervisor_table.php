<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentSupervisorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_supervisor', function (Blueprint $table) {
            $table->id();
            $table->foreignId("office_division_id")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("department_id")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("supervised_by")->references("id")->on("users")->cascadeOnDelete();
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
        Schema::dropIfExists('department_supervisor');
    }
}
