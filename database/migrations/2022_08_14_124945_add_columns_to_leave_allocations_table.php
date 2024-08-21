<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToLeaveAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_allocations', function (Blueprint $table) {
            $table->unsignedInteger('short_day_count')->nullable()->comment('In hour');
            $table->unsignedInteger('half_day_count')->nullable()->comment('In hour');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_allocations', function (Blueprint $table) {
            $table->dropColumn('short_day_count');
            $table->dropColumn('half_day_count');
        });
    }
}
