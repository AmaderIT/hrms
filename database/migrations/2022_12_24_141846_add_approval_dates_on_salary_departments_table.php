<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalDatesOnSalaryDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_department', function (Blueprint $table) {
            $table->timestamp("prepared_date")->nullable()->after('prepared_by');
            $table->timestamp("divisional_approved_date")->nullable()->after('divisional_approval_by');
            $table->timestamp("departmental_approved_date")->nullable()->after('departmental_approval_by');
            $table->timestamp("hr_approved_date")->nullable()->after('hr_approval_by');
            $table->timestamp("accounts_approved_date")->nullable()->after('accounts_approval_by');
            $table->timestamp("managerial_approved_date")->nullable()->after('managerial_approval_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_department', function (Blueprint $table) {
            $table->dropColumn(['prepared_date', 'divisional_approved_date', 'departmental_approved_date', 'hr_approved_date', 'accounts_approved_date', 'managerial_approved_date']);
        });
    }
}
