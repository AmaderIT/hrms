<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $divisionData = array(
            "Barishal", "Rangpur", "Rajshahi", "Khulna", "Sylhet", "Dhaka", "Chattogram", "Mymensingh"
        );

        $divisions = array();
        foreach ($divisionData as $data)
        {
            array_push($divisions, array(
                "name"          => $data,
                "created_at"    => now(),
                "updated_at"    => now()
            ));
        }

        Division::insert($divisions);
    }
}
