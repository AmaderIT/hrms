<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\OfficeDivision;
use App\Models\Promotion;
use App\Models\User;
use App\Models\WorkSlot;
use Illuminate\Database\Seeder;
use Faker\Factory;

class PromotionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        $employees = User::select("id", "email")->get();

        $type = array(Promotion::TYPE_INTERNEE, Promotion::TYPE_PROVISION, Promotion::TYPE_PERMANENT, Promotion::TYPE_PROMOTED);

        $employees->map(function ($employee, $key) use ($faker, $type) {
            $employee->promotions()->create(array(
                "office_division_id"=> rand(1, OfficeDivision::count()),
                "department_id"     => rand(1, Department::count()),
                "designation_id"    => rand(1, Designation::count()),
                "pay_grade_id"      => 1,
                "salary"            => rand(10000, 100000),
                "promoted_date"     => $faker->date(),
                "type"              => $type[array_rand($type, 1)],
                "workslot_id"       => rand(1, WorkSlot::count())
            ));
        });
    }
}
