<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarksColumnOnUserLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_loans', function (Blueprint $table) {
            DB::statement("ALTER TABLE `user_loans` CHANGE `amount_paid` `amount_paid` DECIMAL(11,2) NOT NULL;");
            $table->string('remark')->nullable()->after('is_custom_payment');
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
            DB::statement("ALTER TABLE `user_loans` CHANGE `amount_paid` `amount_paid` INT(11) NOT NULL;");
            $table->dropColumn('remark');
        });
    }
}
