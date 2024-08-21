<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_slots', function (Blueprint $table) {
            $table->id();
            $table->string("title", 50)->unique();
            $table->time("start_time");
            $table->time("end_time")->nullable();
            $table->time("late_count_time")->nullable();
            $table->string("over_time")->default('No');
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
        Schema::dropIfExists('work_slots');
    }
}
