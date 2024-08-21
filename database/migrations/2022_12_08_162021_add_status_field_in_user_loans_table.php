<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusFieldInUserLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_loans', function (Blueprint $table) {
            $table->tinyInteger("status")->after("year")->default(2)->comment('1 = Approved, 2 = Pending, 3 => Rejected');
            $table->bigInteger('created_by')->after("status")->nullable();
            $table->bigInteger('updated_by')->after("created_by")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_loans', function (Blueprint $table) {
            $table->dropColumn("status");
            $table->dropColumn("created_by");
            $table->dropColumn("updated_by");
        });
    }
}
