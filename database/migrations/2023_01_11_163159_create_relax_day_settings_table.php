<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelaxDaySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relax_day_settings', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type')->comment('1=employee, 2=department');
            $table->foreignId("department_id")->references("id")->on("departments")->cascadeOnDelete();
            $table->tinyInteger('max_count_per_month')->comment('Number of relax day(s) count per month');
            $table->string('weekly_days', 50)->comment('Relax Day(s) In Weekly, A textual representation of a day, array element of three letters of day');
            $table->foreignId("created_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("updated_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
            $table->foreignId("deleted_by")->nullable()->references("id")->on("users")->cascadeOnDelete();
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
        Schema::dropIfExists('relax_day_settings');
    }
}
