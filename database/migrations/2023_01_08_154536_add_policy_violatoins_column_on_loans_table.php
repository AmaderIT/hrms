<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPolicyViolatoinsColumnOnLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string('installment_start_month', '50')->nullable()->after('loan_amount');
            $table->string('accept_policy', '50')->nullable()->after('remarks');
            $table->text('policy_violations')->nullable()->after('accept_policy');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('installment_start_month');
            $table->dropColumn('accept_policy');
            $table->dropColumn('policy_violations');
        });
    }
}
