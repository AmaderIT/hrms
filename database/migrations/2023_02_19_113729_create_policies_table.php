<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type')->default(1)->comment('1 = HR Policies, 2 => General Notices, 3 = Events, 4 = Announcement, 5 = Other');
            $table->string('title', '200');
            $table->string('attachment')->nullable();
            $table->integer('order_no')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->nullable()->default(1)->comment('1 = Active, 2 => In-Active');
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
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
        Schema::dropIfExists('policies');
    }
}
