<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("bank_id")->references("id")->on("banks")->cascadeOnDelete();
            $table->foreignId("branch_id")->references("id")->on("branches")->cascadeOnDelete();
            $table->enum("account_type", ["Current", "Deposit", "Saving"])->nullable();
            $table->string("account_name", 50)->nullable();
            $table->string("account_no", 20)->unique()->nullable();
            $table->string("nominee_name", 50)->nullable();
            $table->string("relation_with_nominee", 20)->nullable();
            $table->string("nominee_contact", 30)->nullable();
            $table->double("tax_opening_balance", 10, 2);
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
        Schema::dropIfExists('bank_users');
    }
}
