<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequisitionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requisition_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId("requisition_id")->references("id")->on("requisitions")->cascadeOnDelete();
            $table->foreignId("requisition_item_id")->references("id")->on("requisition_items")->cascadeOnDelete();
            $table->integer("quantity");
            $table->integer("received_quantity")->default(0);
            $table->double("unit_price", 8, 2)->nullable();
            $table->double("gross_price", 8, 2)->nullable();
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
        Schema::dropIfExists('requisition_details');
    }
}
