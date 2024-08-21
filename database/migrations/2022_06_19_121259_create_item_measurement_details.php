<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemMeasurementDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_measurement_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId("requisition_item_id")->references("id")->on("requisition_items")->cascadeOnDelete();
            $table->unsignedInteger('measure_id');
            $table->string('measure_name');
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
        Schema::dropIfExists('item_measurement_details');
    }
}
