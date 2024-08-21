<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonuses', function (Blueprint $table) {
            $table->date('effective_date')->nullable()->after('percentage_of_bonus');
            $table->text('payment_details')->nullable()->after('effective_date');
            $table->tinyInteger('status')->nullable()->default(1)->comment('1 = Active, 2 => In-Active')->after('payment_details');
            $table->bigInteger('created_by')->nullable()->after('status');
            $table->bigInteger('updated_by')->nullable()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonuses', function (Blueprint $table) {
            $table->dropColumn(['effective_date', 'payment_details', 'status', 'created_by', 'updated_by']);
        });
    }
}
