<?php

use App\Models\UserLate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_lates', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->references("id")->on("users")->cascadeOnDelete();
            $table->integer("total_late");
            $table->integer("total_deduction");
            $table->enum("type", [UserLate::TYPE_LEAVE, UserLate::TYPE_SALARY]);
            $table->enum("month", array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12));
            $table->year("year");
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
        Schema::dropIfExists('user_lates');
    }
}
