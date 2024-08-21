<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToInternalTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internal_transfers', function (Blueprint $table) {
            $table->integer("parent_id")->nullable()->comment("It will use for tagging");
            $table->string('reference',100)->nullable();
            $table->integer('gate_pass_checkout')->nullable();
            $table->integer('gate_pass_checkin')->nullable();
            $table->dateTime("checkout_at")->nullable();
            $table->dateTime("checkin_at")->nullable();
            $table->enum("workflow_type", [1, 2])->comment("1 for general and 2 for vendor");
            $table->integer("challan_status")->nullable()->comment("Close or Open or Return Pending");
            $table->tinyInteger("is_return_challan")->default(0);
            $table->tinyInteger("return_status")->default(0)->comment("Returnable or Returned or Not Applicable");
            $table->foreignId("from_supplier_id")->default(0)->unsigned();
            $table->foreignId("to_supplier_id")->default(0)->unsigned();
            $table->text('note')->nullable();
            $table->string('file_attachment_path',500)->nullable();
            $table->foreignId('dispatch_security_checked')->default(0)->unsigned();
            $table->foreignId('receive_security_checked')->default(0)->unsigned();
            $table->text('comment')->nullable();
            $table->foreignId('rejected_by')->default(0)->unsigned();
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
            $table->dropColumn("parent_id");
            $table->dropColumn('reference',100);
            $table->dropColumn("workflow_type");
            $table->dropColumn("challan_status");
            $table->dropColumn("is_return_challan");
            $table->dropColumn("from_supplier_id");
            $table->dropColumn("to_supplier_id");
            $table->dropColumn('remarks');
            $table->dropColumn('file_attachment_path');
            $table->dropColumn('dispatch_security_checked');
            $table->dropColumn('receive_security_checked');
            $table->dropColumn('comment');
            $table->dropColumn('rejected_by');
        });
    }
}
