<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("office_division_id")->references("id")->on("office_divisions")->cascadeOnDelete();
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->foreignId("leave_allocation_details_id")->references("id")->on("leave_allocation_details")->cascadeOnDelete();
            $table->foreignId("leave_type_id")->references("id")->on("leave_types")->cascadeOnDelete();
            $table->boolean('half_day');
            $table->date('from_date');
            $table->date('to_date');
            $table->float('number_of_days', 8, 2);
            $table->foreignId("applied_by")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("authorized_by")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("approved_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->boolean('status')->default(0);
            $table->string('purpose',200);
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
        Schema::dropIfExists('leave_requests');
    }
}
