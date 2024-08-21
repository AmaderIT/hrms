<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CreateOnlineAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('online_attendances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->default(Uuid::uuid4()->toString());
            $table->foreignId("user_id")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("office_division_id")->references("id")->on("office_divisions")->cascadeOnDelete();
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->date('date');
            $table->dateTime("time_in");
            $table->dateTime("time_out")->nullable();
            $table->boolean('status')->default(2)->comment('1 = Approved, 2 = Pending, 3 = Authorized, 99 = Rejected');
            $table->foreignId("applied_by")->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("updated_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("authorized_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("approved_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->timestamps();
            $table->timestamp('authorized_date')->nullable();
            $table->timestamp('approved_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('online_attendances');
    }
}
