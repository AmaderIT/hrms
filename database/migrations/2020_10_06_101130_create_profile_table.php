<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->references("id")->on("users")->cascadeOnDelete();
            $table->enum('gender', ["Male", "Female", "Other"]);
            $table->enum('religion', ["Islam","Hinduism","Christianity","Buddhism", "Other"]);
            $table->date('dob');
            $table->enum('marital_status', ["Single", "Married"]);
            $table->string('emergency_contact', 30);
            $table->string('relation', 30);
            $table->enum("blood_group", ["A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-"]);
            $table->string('nid', 20)->nullable()->unique();
            $table->string('tin', 20)->nullable()->unique();
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
        Schema::dropIfExists('profiles');
    }
}
