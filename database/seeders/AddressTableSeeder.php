<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\District;
use App\Models\Division;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory;

class AddressTableSeeder extends Seeder
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
            $employee->addresses()->createMany(array(
                array(
                    "type"              => Address::TYPE_PRESENT,
                    "address"           => $faker->sentence,
                    "zip"               => $faker->numberBetween(1000,1250),
                    "division_id"       => rand(1, Division::count()),
                    "district_id"       => rand(1, District::count())
                ), array(
                    "type"              => Address::TYPE_PERMANENT,
                    "address"           => $faker->sentence,
                    "zip"               => $faker->numberBetween(1000,1250),
                    "division_id"       => rand(1, Division::count()),
                    "district_id"       => rand(1, District::count())
                )
            ));
        });
    }
}
