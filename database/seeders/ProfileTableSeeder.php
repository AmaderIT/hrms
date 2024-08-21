<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory;

class ProfileTableSeeder extends Seeder
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

        $gender = array(Profile::GENDER_MALE, Profile::GENDER_FEMALE);
        $religion = array(Profile::RELIGION_ISLAM, Profile::RELIGION_HINDU, Profile::RELIGION_BUDDHISM, Profile::RELIGION_CHRISTIANITY, Profile::RELIGION_OTHER);
        $blood_group = array(
            Profile::BLOOD_GROUP_A_POSITIVE, Profile::BLOOD_GROUP_A_NEGATIVE, Profile::BLOOD_GROUP_B_POSITIVE, Profile::BLOOD_GROUP_B_NEGATIVE,
            Profile::BLOOD_GROUP_AB_POSITIVE, Profile::BLOOD_GROUP_AB_NEGATIVE, Profile::BLOOD_GROUP_O_POSITIVE, Profile::BLOOD_GROUP_B_NEGATIVE
        );
        $marital_status = array(Profile::MARITAL_STATUS_SINGLE, Profile::MARITAL_STATUS_MARRIED);

        $employees->map(function ($employee, $key) use ($faker, $gender, $religion, $marital_status, $blood_group) {
            $employee->profile()->create(array(
                "gender"                => $gender[array_rand($gender, 1)],
                "religion"              => $religion[array_rand($religion, 1)],
                "dob"                   => $faker->date(),
                "marital_status"        => $marital_status[array_rand($marital_status, 1)],
                "emergency_contact"     => $faker->numberBetween(111111, 999999) . $faker->numberBetween(11111, 99999),
                "relation"              => "Brother",
                "blood_group"           => $blood_group[array_rand($blood_group, 1)],
                "nid"                   => $faker->numberBetween(11111111, 22222222) . $faker->numberBetween(11111111, 22222222),
                "tin"                   => $faker->numberBetween(11111111, 22222222) . $faker->numberBetween(11111111, 22222222)
            ));
        });
    }
}
