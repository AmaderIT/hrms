<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeptToWarehouseColumnToInternalTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internal_transfers', function (Blueprint $table) {
            $table->boolean('dept_to_ware')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse_column_to_internal_transfers', function (Blueprint $table) {
            $table->dropColumn('dept_to_ware');
        });
    }
}
