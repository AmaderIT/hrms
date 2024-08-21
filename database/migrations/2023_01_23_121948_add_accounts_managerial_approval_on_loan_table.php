<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountsManagerialApprovalOnLoanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            DB::statement("ALTER TABLE `loans` CHANGE `loan_approved_date` `hr_approved_date` DATE NULL DEFAULT NULL, CHANGE `authorized_by` `departmental_approval_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL, CHANGE `loan_authorized_date` `departmental_approved_date` DATE NULL DEFAULT NULL, CHANGE `approved_by` `hr_approval_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL, CHANGE `paid_at` `instalment_paid_at` DATE NULL DEFAULT NULL;");

            $table->tinyInteger("departmental_approval_status")->default(0)->after('departmental_approved_date');
            $table->text("departmental_remarks")->nullable()->after('departmental_approval_status');

            $table->tinyInteger("divisional_approval_status")->default(0)->after('departmental_remarks');
            $table->foreignId("divisional_approval_by")->nullable()->after('divisional_approval_status')->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp("divisional_approved_date")->nullable()->after('divisional_approval_by');
            $table->text("divisional_remarks")->nullable()->after('divisional_approved_date');

            $table->tinyInteger("hr_approval_status")->default(0)->after('divisional_remarks');
            $table->text("hr_remarks")->nullable()->after('hr_approved_date');

            $table->tinyInteger("accounts_approval_status")->default(0)->after('hr_remarks');
            $table->foreignId("accounts_approval_by")->nullable()->after('accounts_approval_status')->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp("accounts_approved_date")->nullable()->after('accounts_approval_by');
            $table->text("accounts_remarks")->nullable()->after('accounts_approved_date');

            $table->tinyInteger("managerial_approval_status")->default(0)->after('accounts_remarks');
            $table->foreignId("managerial_approval_by")->nullable()->after('managerial_approval_status')->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp("managerial_approved_date")->nullable()->after('managerial_approval_by');
            $table->text("managerial_remarks")->nullable()->after('managerial_approved_date');

            $table->tinyInteger("loan_paid_status")->default(0)->after('managerial_remarks');
            $table->foreignId("loan_paid_by")->nullable()->after('loan_paid_status')->references("id")->on("users")->cascadeOnDelete();
            $table->timestamp("loan_paid_date")->nullable()->after('loan_paid_by');

            DB::statement("ALTER TABLE `loans` CHANGE `status` `status` ENUM('Active','Paid','Pending','Reject') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
            DB::statement("ALTER TABLE `loans` CHANGE `departmental_approved_date` `departmental_approved_date` TIMESTAMP NULL DEFAULT NULL, CHANGE `hr_approved_date` `hr_approved_date` TIMESTAMP NULL DEFAULT NULL, CHANGE `instalment_paid_at` `instalment_paid_at` TIMESTAMP NULL DEFAULT NULL;");
            DB::statement("UPDATE `permissions` SET `name`='Loan Amount Payment' WHERE `name` = 'Pay Loans'");
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
            DB::statement("ALTER TABLE `loans` CHANGE `hr_approved_date` `loan_approved_date` DATE NULL DEFAULT NULL, CHANGE `departmental_approval_by` `authorized_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL, CHANGE `departmental_approved_date` `loan_authorized_date` DATE NULL DEFAULT NULL, CHANGE `hr_approval_by` `approved_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL, CHANGE `instalment_paid_at` `paid_at` DATE NULL DEFAULT NULL;");

            $table->dropColumn("departmental_approval_status");
            $table->dropColumn("departmental_remarks");

            $table->dropColumn("divisional_approval_status");
            $table->dropConstrainedForeignId('divisional_approval_by');
            $table->dropColumn("divisional_approved_date");
            $table->dropColumn("divisional_remarks");

            $table->dropColumn("hr_approval_status");
            $table->dropColumn("hr_remarks");

            $table->dropColumn("accounts_approval_status");
            $table->dropConstrainedForeignId("accounts_approval_by");
            $table->dropColumn("accounts_approved_date");
            $table->dropColumn("accounts_remarks");

            $table->dropColumn("managerial_approval_status");
            $table->dropConstrainedForeignId("managerial_approval_by");
            $table->dropColumn("managerial_approved_date");
            $table->dropColumn("managerial_remarks");

            $table->dropColumn("loan_paid_status");
            $table->dropConstrainedForeignId("loan_paid_by");
            $table->dropColumn("loan_paid_date");

            DB::statement("ALTER TABLE `loans` CHANGE `status` `status` ENUM('Active','Paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
            DB::statement("ALTER TABLE `loans` CHANGE `departmental_approved_date` `departmental_approved_date` DATE NULL DEFAULT NULL, CHANGE `hr_approved_date` `hr_approved_date` DATE NULL DEFAULT NULL, CHANGE `loan_paid_date` `loan_paid_date` DATE NULL DEFAULT NULL;");
            DB::statement("UPDATE `permissions` SET `name`='Pay Loans' WHERE `name` = 'Loan Amount Payment'");
        });
    }
}
