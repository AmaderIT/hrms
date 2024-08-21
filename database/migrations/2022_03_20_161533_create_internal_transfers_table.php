<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternalTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internal_transfers', function (Blueprint $table) {
            $table->id();
            $table->string("type",20);
            $table->string("challan",20);
            $table->integer("status")->default(0);
            $table->foreignId('source_warehouse_id')->default(0)->unsigned();
            $table->foreignId('source_department_id')->default(0)->unsigned();
            $table->foreignId('destination_department_id')->default(0)->unsigned();
            $table->boolean("is_returnable")->default(0);
            $table->foreignId('authorized_by')->default(0)->unsigned();
            $table->foreignId('security_checked_by')->default(0)->unsigned();
            $table->foreignId('delivered_by')->default(0)->unsigned();
            $table->foreignId('received_by')->default(0)->unsigned();
            $table->string("mode_of_transport",100)->nullable();
            $table->dateTime("issue_at")->nullable();
            $table->foreignId('created_by')->references("id")->on("users")->cascadeOnDelete();;
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
        Schema::dropIfExists('internal_transfer');
    }
}
