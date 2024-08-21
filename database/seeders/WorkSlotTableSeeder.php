<?php

namespace Database\Seeders;

use App\Models\WorkSlot;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Seeder;

class WorkSlotTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WorkSlot::create(array(
            "title"         => "Day",
            "start_time"    => Carbon::createFromTime(10)->format("H:i:s"),
            "end_time"      => Carbon::createFromTime(19)->format("H:i:s"),
        ));
    }
}
