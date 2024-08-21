<?php

use App\Models\Loan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId("office_division_id")->references("id")->on("office_divisions")->cascadeOnDelete();
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->foreignId("user_id")->references("id")->on("users")->cascadeOnDelete();
            $table->enum("type", [Loan::TYPE_LOAN, Loan::TYPE_ADVANCE]);
            $table->integer("loan_amount");
            $table->integer("loan_tenure");
            $table->double("installment_amount", 8, 2);
            $table->date("loan_approved_date");
            $table->string("remarks", 255)->nullable();
            $table->foreignId("approved_by")->references("id")->on("users")->cascadeOnDelete();
            $table->enum("status", array(Loan::STATUS_ACTIVE, Loan::STATUS_PAID));
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
        Schema::dropIfExists('loans');
    }
}
