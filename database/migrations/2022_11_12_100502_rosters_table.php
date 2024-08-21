<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RostersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        #Table: roasters (Employee Roaster's Master Table)
        Schema::create('rosters', function (Blueprint $table) {
            $table->uuid('id');
            $table->tinyInteger('type')->comment('1=employee, 2=department')->default(1);
            $table->unsignedBigInteger("company_id");
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->foreignId("user_id")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("work_slot_id")->references("id")->on("work_slots")->cascadeOnDelete();
            $table->foreignId("office_division_id")->comment('Not Applicable')->references("id")->on("office_divisions")->cascadeOnDelete();
            $table->tinyInteger('is_weekly_holiday')->comment('0=No, 1=Yes')->default(0);
            $table->date("active_date");
            $table->tinyInteger('is_locked')->comment('0=Free, 1=Locked')->default(1);
            $table->foreignId("approved_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp('approved_date')->nullable();
            $table->boolean('status')->comment('0=Pending, 1=Approved, 2=Cancel')->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId("created_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("updated_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("deleted_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
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
        Schema::dropIfExists('rosters');
    }
}
