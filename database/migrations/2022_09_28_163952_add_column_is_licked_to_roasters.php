<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsLickedToRoasters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roasters', function (Blueprint $table) {
            $table->tinyInteger('is_locked')->comment('0=Free, 1=Locked')->default(1)->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roasters', function (Blueprint $table) {
            $table->dropColumn('is_locked');
        });
    }
}
