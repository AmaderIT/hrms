<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnProvisionalDurationOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->date('provision_end_date')
                ->after('created_at')
                ->nullable();
            $table->unsignedTinyInteger('provision_duration')
                ->after('provision_end_date')
                ->default(6)
                ->comment("no of month from joining date");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('provisional_duration');
        });
    }
}
