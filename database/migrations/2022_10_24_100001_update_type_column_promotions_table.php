<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTypeColumnPromotionsTable extends Migration
{
    public $set_schema_table = 'promotions';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE ".$this->set_schema_table." MODIFY COLUMN type ENUM('Internee', 'Provision', 'Permanent','Promoted','Contractual','Transferred','Increment','Rejoin','Terminated','Join') NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
