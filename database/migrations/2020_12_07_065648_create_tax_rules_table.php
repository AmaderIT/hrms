<?php

use App\Models\TaxRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId("tax_id")->references("id")->on("taxes")->cascadeOnDelete();
            $table->integer("slab");
            $table->integer("rate");
            $table->enum("gender", [TaxRule::GENDER_MALE, TaxRule::GENDER_FEMALE, TaxRule::TYPE_REBATE]);
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
        Schema::dropIfExists('tax_rules');
    }
}
