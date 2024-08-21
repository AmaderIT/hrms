<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string("name", 100);
            $table->string("email", 100)->nullable();
            $table->string("phone", 30)->unique();
            $table->string("fingerprint_no", 10)->nullable()->unique();
            $table->string("photo")->nullable();
            $table->boolean("status")->default(1);
            $table->boolean("is_supervisor")->default(0);
            $table->timestamp("email_verified_at")->nullable();
            $table->string("password", 100);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
