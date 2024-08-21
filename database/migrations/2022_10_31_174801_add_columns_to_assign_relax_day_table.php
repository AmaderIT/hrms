<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToAssignRelaxDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assign_relax_day', function (Blueprint $table) {
            $table->tinyInteger('approval_status')->default(0);
            $table->unsignedInteger('approved_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assign_relax_day', function (Blueprint $table) {
            $table->dropColumn('approval_status');
            $table->dropColumn('approved_by');
        });
    }
}
