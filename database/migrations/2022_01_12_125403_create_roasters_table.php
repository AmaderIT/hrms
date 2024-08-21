<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roasters', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("work_slot_id")->references("id")->on("work_slots")->cascadeOnDelete();
            $table->foreignId("office_division_id")->references("id")->on("office_divisions")->cascadeOnDelete();
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->string("weekly_holidays",70);
            $table->date("active_from");
            $table->date("end_date");
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
        Schema::dropIfExists('roasters');
    }
}
