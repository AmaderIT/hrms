<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\OfficeDivision;
use Illuminate\Database\Seeder;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Department::create(array(
            "office_division_id"    => OfficeDivision::first()->id,
            "name"                  => "Admin",
        ));
    }
}
