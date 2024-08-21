<?php

use App\Models\Bonus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId("office_division_id")->references("id")->on("office_divisions")->cascadeOnDelete();
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->foreignId("bonus_id")->references("id")->on("bonuses")->cascadeOnDelete();
            $table->foreignId("user_id")->references("id")->on("users")->cascadeOnDelete();
            $table->double("amount", 10, 2);
            $table->double("tax", 10, 2);
            $table->enum("status", [Bonus::STATUS_UNPAID, Bonus::STATUS_PAID]);
            $table->enum("month", [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
            $table->year("year");
            $table->timestamp("paid_at", 0)->nullable();
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
        Schema::dropIfExists('user_bonus');
    }
}
