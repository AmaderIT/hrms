<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string("memo_no", 30)->unique();
            $table->string("level", 20);
            $table->string("subject", 100);
            $table->text("description");
            $table->foreignId('warned_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->references('id')->on('users')->cascadeOnDelete();
            $table->date("warning_date");
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
        Schema::dropIfExists('warnings');
    }
}
