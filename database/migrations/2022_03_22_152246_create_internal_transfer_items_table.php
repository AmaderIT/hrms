<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternalTransferItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internal_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internal_transfer_id')->references('id')->on('internal_transfers')->cascadeOnDelete();
            $table->string('operation_type');
            $table->foreignId('item_id')->references('id')->on('requisition_items')->cascadeOnDelete();
            $table->integer('qty');
            $table->string('uom');
            $table->text('remarks')->nullable();
            $table->string('shelf_no')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internal_transfer_items');
    }
}
