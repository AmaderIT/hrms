<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveAllocationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_allocation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId("leave_allocation_id")->references("id")->on("leave_allocations")->cascadeOnDelete();
            $table->foreignId("leave_type_id")->references("id")->on("leave_types")->cascadeOnDelete();
            $table->integer("total_days");
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
        Schema::dropIfExists('leave_allocation_details');
    }
}
