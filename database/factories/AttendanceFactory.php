<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "user_id"       => rand(1, User::count()),
            "department_id" => rand(1, Department::count()),
            "log_time"      => $this->faker->time(),
            "device_id"     => $this->faker->randomDigitNotNull
        ];
    }
}
