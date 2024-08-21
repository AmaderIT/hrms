<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAndAddColumnToInternalTransferItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internal_transfer_items', function (Blueprint $table) {
            $table->string('uom')->nullable()->change();
            $table->string('item_type');
            $table->unsignedInteger('measure_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internal_transfer_items', function (Blueprint $table) {
            $table->string('uom')->change();
            $table->dropColumn('item_type');
            $table->dropColumn('measure_id');
        });
    }
}
