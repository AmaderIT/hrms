<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnInRelaxDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('relax_day', function (Blueprint $table) {
            $table->tinyInteger("type")->comment('1=employee, 2=department')->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('relax_day', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
