<?php

namespace Database\Seeders;

use App\Models\PayGrade;
use Illuminate\Database\Seeder;

class PayGradeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PayGrade::create(array(
            "name"                  => "A",
            "range_start_from"      => 20000,
            "range_end_to"          => 50000,
            "percentage_of_basic"   => 60,
            "overtime_per_hour"     => 300,
            "tax_id"                => null
        ));
    }
}
