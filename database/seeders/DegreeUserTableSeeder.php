<?php

namespace Database\Seeders;

use App\Models\Degree;
use App\Models\Institute;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class DegreeUserTableSeeder extends Seeder
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

        $employees->map(function ($employee, $key) use ($faker) {
            $employee->degrees()->attach(array([
                "user_id"       => $employee->id,
                "degree_id"     => rand(1, Degree::count()),
                "institute_id"  => rand(1, Institute::count()),
                "passing_year"  => $faker->year,
                "result"        => $faker->numberBetween(3, 5)
            ]));
        });
    }
}
