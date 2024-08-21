<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToInternalTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internal_transfers', function (Blueprint $table) {
            $table->foreignId('destination_warehouse_id')->default(0)->unsigned()->after('destination_department_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internal_transfers', function (Blueprint $table) {
            $table->dropColumn('destination_warehouse_id');
        });
    }
}
