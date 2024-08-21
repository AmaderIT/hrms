<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequisitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id()->startingValue(100000);
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->foreignId("applied_by")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("approved_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->date("applied_date");
            $table->text('remarks');
            $table->enum("priority", [0, 1, 2, 3]);
            $table->enum("status", [0, 1, 2, 3]);
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
        Schema::dropIfExists('requisitions');
    }
}
