<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class UpdateUserBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_bonuses', function (Blueprint $table) {
            $table->uuid('uuid')->default(Uuid::uuid4()->toString())->after('id');
            $table->foreignId("bonus_department_id")->after('uuid');
            $table->foreignId("designation_id")->after('user_id');
            $table->foreignId("pay_grade_id")->after('designation_id');
            $table->foreignId("tax_id")->after('pay_grade_id')->nullable();
            $table->double("gross", 10, 2)->default(0)->after('tax_id');
            $table->double("basic", 10, 2)->default(0)->after('gross');
            $table->double("house_rent", 10, 2)->default(0)->after('basic');
            $table->double("medical_allowance", 10, 2)->default(0)->after('house_rent');
            $table->double("conveyance", 10, 2)->default(0)->after('medical_allowance');
            $table->double("net_payable_amount", 10, 2)->default(0)->after('tax');
            $table->string("payment_mode", 20)->after('year')->nullable();
            $table->text("remarks")->after('payment_mode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_bonuses', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'bonus_department_id', 'designation_id', 'pay_grade_id', 'tax_id', 'payment_mode', 'remarks']);
        });
    }
}
