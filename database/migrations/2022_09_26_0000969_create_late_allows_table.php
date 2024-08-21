<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLateAllowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('late_allows', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("user_id");
            $table->tinyInteger("allow");
            $table->unsignedInteger("allowed_by")->nullable();
            $table->timestamp("allowed_date")->nullable();
            $table->unsignedInteger("replaced_by")->nullable();
            $table->timestamp("replaced_date")->nullable();
            $table->tinyInteger("is_active")->default(1);
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
        Schema::dropIfExists('late_allows');
    }
}
