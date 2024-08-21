<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DivisionTableSeeder::class,
            DistrictTableSeeder::class,
            DegreeTableSeeder::class,
            BankTableSeeder::class,
            BranchTableSeeder::class,
            OfficeDivisionTableSeeder::class,
            DepartmentTableSeeder::class,
            DesignationTableSeeder::class,
            WorkSlotTableSeeder::class,
            InstituteTableSeeder::class,
            RoleTableSeeder::class,
            UserTableSeeder::class,
            ProfileTableSeeder::class,
            AddressTableSeeder::class,
            PayGradeTableSeeder::class,
            PromotionTableSeeder::class,
            ActionReasonSeeder::class,
            EmployeeStatusTableSeeder::class,
            SupervisorTableSeeder::class,
            DegreeUserTableSeeder::class,
            PermissionTableSeeder::class,
            SettingTableSeeder::class,
            AdditionalSettingSeeder::class,
        ]);
    }
}
