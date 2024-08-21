<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\BankUser;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BankUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "user_id"               => rand(1, User::count()),
            "bank_id"               => rand(1, Bank::count()),
            "branch_id"             => rand(1, Branch::count()),
            "account_name"          => $this->faker->name,
            "account_no"            => $this->faker->bankAccountNumber,
            "account_type"          => "Saving",
            "account_name"          => $this->faker->name,
            "account_no"            => $this->faker->numberBetween(111111, 999999) . $this->faker->numberBetween(111111, 999999),
            "nominee_name"          => $this->faker->name,
            "relation_with_nominee" => "Mother",
            "nominee_contact"       => $this->faker->phoneNumber
        ];
    }
}
