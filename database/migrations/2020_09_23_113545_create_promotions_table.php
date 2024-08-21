<?php

use App\Models\Promotion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $types = [
            Promotion::TYPE_INTERNEE,
            Promotion::TYPE_PROVISION,
            Promotion::TYPE_PERMANENT,
            Promotion::TYPE_PROMOTED,
            Promotion::TYPE_CONTRACTUAL,
            Promotion::TYPE_TRANSFERRED
        ];

        Schema::create('promotions', function (Blueprint $table) use ($types) {
            $table->id();
            $table->foreignId("user_id")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("office_division_id")->references("id")->on("office_divisions")->cascadeOnDelete();
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->foreignId("designation_id")->references("id")->on("designations")->cascadeOnDelete();
            $table->foreignId("pay_grade_id")->references("id")->on("pay_grades")->cascadeOnDelete();
            $table->double("salary");
            $table->date("promoted_date")->nullable();
            $table->enum("type", $types);
            $table->foreignId("workslot_id")->references("id")->on("work_slots")->cascadeOnDelete();
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
        Schema::dropIfExists('promotions');
    }
}
