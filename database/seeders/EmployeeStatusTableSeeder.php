<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class EmployeeStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        $employees = User::get();
        $supervisors = User::where("is_supervisor", 1)->pluck("id")->toArray();

        $employees->map(function ($employee, $key) use ($faker, $supervisors) {
            $employee->employeeStatus()->create(array(
                "action_reason_id"      => 2,
                "action_taken_by"       => $supervisors[array_rand($supervisors, 1)],
                "action_date"           => $faker->date()
            ));
        });
    }
}
