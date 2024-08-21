<?php

namespace Database\Factories;

use App\Models\WorkSlot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WorkSlotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkSlot::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "title" => $this->faker->unique()->word,
            "start_time" => $this->faker->time(),
            "end_time" => $this->faker->time(),
            "late_count_time" => $this->faker->time()
        ];
    }
}
