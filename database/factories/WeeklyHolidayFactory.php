<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\WeeklyHoliday;
use Illuminate\Database\Eloquent\Factories\Factory;

class WeeklyHolidayFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WeeklyHoliday::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "department_id" => rand(1, Department::count()),
            "days"          => '["fri","sat"]'
        ];
    }
}
