<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySomeFieldsNullableInDegreeUsersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('degree_user', function (Blueprint $table) {
            $table->foreignId('degree_id')->nullable()->change();
            $table->foreignId('institute_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('degree_user', function (Blueprint $table) {
            //
        });
    }
}
