<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentRoastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_roasters', function (Blueprint $table) {
            $table->id();
            $table->foreignId("office_division_id")->references("id")->on("office_divisions")->cascadeOnDelete();
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->foreignId("work_slot_id")->references("id")->on("work_slots")->cascadeOnDelete();
            $table->string("weekly_holidays",70)->nullable();
            $table->date("active_from");
            $table->date("end_date");
            $table->tinyInteger('is_locked')->comment('0=Free, 1=Locked')->default(1);
            $table->foreignId("created_by")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("approved_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp('approved_date')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId("updated_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("deleted_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
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
        Schema::dropIfExists('department_roasters');
    }
}
