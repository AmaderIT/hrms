<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LeaveRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "user_id"       => rand(1, User::count()),
            "leave_type_id" => rand(1, LeaveType::count()),
            "half_day"      => $this->faker->boolean,
            "from_date"     => $this->faker->date(),
            "to_date"       => $this->faker->date(),
            "number_of_days"=> rand(1, 5),
            "applied_to"    => rand(1, User::count()),
            "approved_by"   => rand(1, User::count()),
            "status"        => $this->faker->boolean,
            "purpose"       => $this->faker->sentence
        ];
    }
}
