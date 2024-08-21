<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersDailyWorkSlotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_daily_work_slot', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->id();
            $table->unsignedInteger('user_id');
            $table->date('date');
            $table->unsignedInteger('work_slot_id');
            $table->string('work_slot_title',255)->nullable();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->time('late_count_time')->nullable();
            $table->tinyInteger('is_flexible');
            $table->string('over_time',10);
            $table->unsignedInteger('total_work_hour')->nullable();
            $table->time('overtime_count')->nullable();
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
        Schema::dropIfExists('users_daily_work_slot');
    }
}
