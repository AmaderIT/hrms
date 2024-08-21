<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Warning;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarningFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Warning::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "user_id"       => rand(1, User::count()),
            "memo_no"       => "mem-warn-" . mt_rand(100000, 999999),
            "level"         => $this->faker->word,
            "subject"       => $this->faker->word,
            "description"   => $this->faker->text,
            "warned_by"     => rand(1, User::count()),
            "updated_by"    => rand(1, User::count()),
            "warning_date"  => $this->faker->date(),
        ];
    }
}
