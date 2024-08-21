<?php

namespace Database\Seeders;

use App\Models\OfficeDivision;
use Illuminate\Database\Seeder;

class OfficeDivisionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $officeDivisionData = array(
            "Software Engineering"
        );

        $officeDivisions = array();
        foreach ($officeDivisionData as $data)
        {
            array_push($officeDivisions, array(
                "name"          => $data,
                "created_at"    => now(),
                "updated_at"    => now()
            ));
        }

        OfficeDivision::insert($officeDivisions);
    }
}
