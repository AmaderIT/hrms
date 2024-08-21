<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InternalTransferSourceWarehouseReject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internal_transfer_source_warehouse_reject', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('internal_transfer_id');
            $table->boolean('serve_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internal_transfer_source_warehouse_reject');
    }
}
