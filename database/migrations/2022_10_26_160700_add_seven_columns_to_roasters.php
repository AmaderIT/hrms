<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSevenColumnsToRoasters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roasters', function (Blueprint $table) {
            $table->foreignId("created_by")->nullable()->references("id")->on("users")->cascadeOnDelete()->after('is_locked');
            $table->foreignId("approved_by")->nullable()->references("id")->on("users")->cascadeOnDelete()->after('created_by');
            $table->timestamp('approved_date')->nullable()->after('approved_by');
            $table->boolean('approval_status')->comment('0=Pending, 1=Approved, 2=Cancel')->default(0)->after('approved_date');
            $table->text('remarks')->nullable()->after('approval_status');
            $table->bigInteger("department_roaster_id")->nullable()->references("id")->on("department_roasters")->comment('FK:department_roasters.id')->after('remarks');
            $table->foreignId("updated_by")->nullable()->references("id")->on("users")->cascadeOnDelete()->after('department_roaster_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roasters', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('approved_by');
            $table->dropColumn('approved_date');
            $table->dropColumn('approval_status');
            $table->dropColumn('remarks');
            $table->dropColumn('department_roaster_id');
            $table->dropColumn('updated_by');
        });
    }
}
