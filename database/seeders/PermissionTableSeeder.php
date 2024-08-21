<?php

namespace Database\Seeders;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionTableSeeder extends Seeder
{
    /**
     * @throws BindingResolutionException
     */
    public function run()
    {
        # Remove existing cache related to laravel permission
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        Schema::disableForeignKeyConstraints();
        DB::table('permissions')->truncate();
        Schema::enableForeignKeyConstraints();

        $permissions = [
            # Division
            'Create New Divisions',
            'Edit Divisions Name',
            'Delete Divisions',
            'View Division List',

            # District
            'Create New District',
            'Edit Districts Name',
            'Delete District Name',
            'View District List',

            # Degree
            'Create New Degree',
            'Edit Degree Name',
            'Delete Degree Name',
            'View Degree List',

            # Institute
            'Create New Institute',
            'Edit Institute Name',
            'Delete Institute Name',
            'View Institute List',

            # Bank
            'Create New Bank',
            'Edit Bank Name',
            'Delete Bank Name',
            'View Bank List',

            # Branch
            'Create New Bank Branch',
            'Edit Bank Branch Name',
            'Delete Bank Branch Name',
            'View Bank Branch List',

            # Department
            'Create New Department',
            'Edit Department Name',
            'Delete Department Name',
            'View Department List',

            # Designation
            'Create New Designation',
            'Edit Designation',
            'Delete Designation',
            'View Designation List',

            # Supervisor
            'Create Supervisor',
            'Delete Supervisor',
            'View Supervisor List',

            # Work Slot
            'Create Workslot',
            'Edit Workslot',
            'Delete Workslot',
            'View Workslot List',

            # Action Type
            'Create Action type',
            'Edit Action type',
            'Delete Action type',
            'View Action type',

            # Action Reason
            'Create Action Reason',
            'Edit Action Reason',
            'Delete Action Reason',
            'View Action Reason',

            # Employee
            'Create New Employee',
            'Edit Employee Info',
            'View Employee List',
            'Change Employee Status',
            'Change Employee Password',
            'Import Employee Info',
            'Export Employee Info',

            # Promotion
            'Create Promotion',
            'Edit Promotion',
            'Delete Promotion',
            'View Promotion List',

            # Termination
            'Create Termination',
            'Termination List',
            'Termination Edit',
            'Termination Delete',

            # Holiday
            'Add Holidays',
            'Edit Holidays',
            'Delete Holidays',
            'View Holiday List',

            # Public Holiday
            'Add Public Holidays',
            'Edit Public Holidays',
            'Delete Public Holidays',
            'View Public Holidays',

            # Weekly Holiday
            'Add Weekly Holiday',
            'Edit Weekly Holiday',
            'Delete Weekly Holiday',
            'View Weekly Holiday',

            # Leave Type
            'Create Leave Type',
            'Edit Leave Type',
            'Delete Leave Type',
            'View Leave Type',

            # Roles
            'Create Role',
            'Edit Role',
            'Delete Role',
            'View Role List',

            # Permission
            'Permission List',

            # Apply for Leave
            'Create Leave Application',
            'Edit Leave Application',
            'Delete Leave Application',
            'View Leave Application',

            # Requested Application
            'Leave Application List',

            # Activity Log
            'View Activity Log',

            # Dashboard
            'View Dashboard Employee List',

            # Office Division
            'Create Office Division',
            'Edit Office Division',
            'Delete Office Division',
            'View Office Division List',

            # Earning
            'Create Earnings',
            'Edit Earnings',
            'Delete Earnings',
            'View Earnings List',

            # Deduction
            'Create Deductions',
            'Edit Deductions',
            'Delete Deductions',
            'View Deductions List',

            # Pay Grade
            'Create Pay Grade',
            'Edit Pay Grade',
            'Delete Pay Grade',
            'View Pay Grade List',

            # Bonus
            'Create Bonus',
            'Edit Bonus',
            'Delete Bonus',
            'View Bonus List',

            # Generate Bonus
            'Generate Bonus',
            'Edit Generated Bonus',
            'Delete Generated Bonus',
            'View Generated Bonus List',

            # Tax
            'Create Tax',
            'Edit Tax',
            'Delete Tax',
            'View Tax List',
            'Change Tax Status',

            # Tax Rules
            'Edit Tax Rules',

            # Loan
            'Apply for Loans',
            'Edit Loans',
            'Delete Loans',
            'View Loan List',

            # Employee Loan
            'Pay Installment Amount',

            # Leave Status
            'View Leave Status',

            # Leave Allocation
            'Create Leave Allocation',
            'Edit Leave Allocation',
            'Delete Leave Allocation',
            'View Leave Allocation List',

            # Salary
            "Prepare Salary",
            "View Salary",
            "Pay Salary",
            "Generate Pay Slip",
            "Download PDF",

            # Delete Employee
            "Delete Employee",
            "Show Admin Dashboard",
            "Show Supervisor Dashboard",
            "Show Employee Dashboard",
            "Show Salary",

            # Report
            "Generate Attendance Report",
            "Generate Salary Report",
            "Generate Unpaid Leave Report",

            "Show Employee Leave Applications",
            "Create Employee Leave Application",
            "Edit Employee Leave Application",
            "Delete Employee Leave Application",

            "Filter option for Employee List",
            "Incomplete Biometric Data",

            # Requisition
            "Create Requisition",
            "Edit Requisition",
            "Delete Requisition",
            "View Requisition",
            "Approve Requisition",

            "Sync Employee to Attendance Device",
            "Settings",
            "Employee by Pay Grade",
            "Pay Salary by Department",

            # Devices
            'Create Devices',
            'Edit Devices',
            'Delete Devices',
            'View Devices List',

            "Create Daily Attendance",

            # Bonus
            'Create Tax Customization',
            'Edit Tax Customization',
            'Delete Tax Customization',
            'View Tax Customization List',

            # Meals
            'View Active Meal Consumers',
            'View Meal Reports',
            'Generate Meal Report Pdf',
            'Generate Meal Report Csv',

            'VIEW TAX HISTORY',
            'Search For Employee Tax',
            'View Salary Yearly History',

            # Leave Report
            'Generate Leave Report',

            # Role User List
            'View Role User List',

            # Employee-List Reset Password
            'Reset Employee Password',

            'Generate Attendance Report to Supervisor',
            'Authorize Leave Requests',
            'Approve Leave Requests',

            # My Requisition
            "Create My Requisition",
            "Edit My Requisition",
            "Delete My Requisition",
            "View My Requisition",

            # Download Requisition
            "Download Requisition",

            # Sync Employee Leave Balance
            "Sync Employee Leave Balance",

            # Late Management
            "View Late Management",
            "Edit Late Management",
            "View User Late",

            # Export Employee Profile
            "Export Employee Profile",

            # Copy data to Another Year
            "Copy data to Another Year",

            # Copy Tax
            "Copy Tax",

            # View Late Status
            "View Late Status",

            # Update Employees Role
            "Update Employees Role",

            # Roaster
            'Create New Roasters',
            'Edit Roasters',
            'Delete Roasters',
            'View Roaster List',

            "Generate Daily Attendance",

            # Warehouse
            'Create New Warehouse',
            'Edit Warehouse Name',
            'Delete Warehouse Name',
            'View Warehouse List',

            # Unit
            'Create New Unit',
            'Edit Unit Name',
            'Delete Unit Name',
            'View Unit List',

            # Internal Transfer
            'Create Internal Transfer',
            'Edit Internal Transfer',
            'Delete Internal Transfer',
            'List Internal Transfer',
            'Authorize Internal Transfer',
            'Security Check Internal Transfer',
            'Delivery Internal Transfer',
            'Receive Internal Transfer',

            # Requisition Items
            'Create New Requisition Item',
            'Edit Requisition Item Name',
            'Delete Requisition Item Name',
            'View Requisition Item List',

            # Division Supervisor
            'Create Division Supervisor',
            'Delete Division Supervisor',
            'View Division Supervisor List',

            'Filter option for Requisition List',
            'Export Requisition',

            # Internal Transfer
            'Print Internal Transfer',
            'Detail Internal Transfer',
            'Security CheckOut Internal Transfer',
            'Security CheckIn Internal Transfer',
            'Download Internal Transfer Attachment',
            'Return Internal Transfer',
            'Can Internal Transfer Approve',
            'Reject Internal Transfer',
            'Download Internal Transfer Report',
            'View All Departments Internal Transfer',
            'Create All Departments Internal Transfer',

            # Requisition Item
            'Sync Requisition Item',
            'Send Data to WHMS',

            # Other Requisition Items
            'View Other Requisition Item List',
            'Create Other Requisition Item',
            'Edit Other Requisition Item',
            'Delete Other Requisition Item',

            #Monthly Attendance Report
            'Department Wise OR Individual Monthly Attendance Report',
            'Department Wise OR Individual Monthly Attendance Report Download',

            #Yearly Attendance Report
            'Department Wise OR Individual Yearly Attendance Report',
            'Department Wise OR Individual Yearly Attendance Report Download',

            #Edit Salary
            'Edit Employee Salary',

            #Monthly Timebase Attendance Report
            'Department Wise OR Individual Timebase Monthly Attendance Report',
            'Department Wise OR Individual Timebase Monthly Attendance Report Download',


            #Transfer
            'Create Transfer',
            'Edit Transfer',
            'Update Transfer',
            'View Transfer List',
            'View Transfer History',

            #Supervisor Dashboard Component
            'Supervisor Dashboard Total Employee',
            'Supervisor Dashboard Total In Leave Today',
            'Supervisor Dashboard Total In Leave Tomorrow',
            'Supervisor Dashboard Today Present',
            'Supervisor Dashboard Today Absent',
            'Supervisor Dashboard Today Late',
            'View Transfer History',

            # Salary Approval Steps
            'Salary Divisional Approval',
            'Salary Departmental Approval',
            'Salary HR Approval',
            'Salary Accounts Approval',
            'Salary Managerial Approval',

            'Supervisor Dashboard Today Late',

            #ROASTER PERMISSION
            'Roaster Unlock Button',
            'Roaster Approval Permission'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
