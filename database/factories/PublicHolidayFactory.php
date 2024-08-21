<?php

namespace Database\Factories;

use App\Models\Holiday;
use App\Models\PublicHoliday;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicHolidayFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PublicHoliday::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "holiday_id"=> rand(1, Holiday::count()),
            "from_date" => $this->faker->date(),
            "to_date"   => $this->faker->date(),
            "remarks"   => $this->faker->sentence
        ];
    }
}
