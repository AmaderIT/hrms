<?php

use App\Models\Loan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuthorizationFieldsInLoanTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->uuid("uuid")->after("id")->index();
            $table->foreignId('authorized_by')->nullable()->after("remarks");
            $table->date("loan_authorized_date")->nullable()->after("authorized_by");
            $table->foreignId('approved_by')->nullable()->change();
            $table->date("loan_approved_date")->nullable()->change();
            $table->softDeletes()->after("updated_at");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn("uuid");
            $table->dropColumn("authorized_by");
            $table->dropColumn("loan_authorized_date");
            $table->foreignId('approved_by')->change();
            $table->date("loan_approved_date")->change();
            $table->dropColumn("deleted_at");
        });
    }
}
