<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSyncDeviceDefaultValOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `sync_device` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'This field identify employee registration into attendance device server is true or false, 1 use for registration success, 0 use for not success' AFTER `payment_mode`");
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
            //
        });
    }
}
