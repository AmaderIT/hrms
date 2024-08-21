<?php

namespace Database\Factories;

use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Leave::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "leave_type_id" => rand(1, LeaveType::count()),
            "number_of_days"=> rand(1, 3),
            "year"          => $this->faker->year
        ];
    }
}
