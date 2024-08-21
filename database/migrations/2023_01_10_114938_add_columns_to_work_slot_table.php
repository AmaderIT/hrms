<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToWorkSlotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_slots', function (Blueprint $table) {
            $table->tinyInteger('is_flexible')->default(0);
            $table->unsignedInteger('total_work_hour')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_slots', function (Blueprint $table) {
            $table->dropColumn('is_flexible');
            $table->dropColumn('total_work_hour');
        });
    }
}
