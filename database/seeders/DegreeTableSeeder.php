<?php

namespace Database\Seeders;

use App\Models\Degree;
use Illuminate\Database\Seeder;

class DegreeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $degreeData = array(
            "PSC/Equivalent", "JSC/JDC/Equivalent", "SSC/Equivalent",
            "HSC/Equivalent", "BA(Bachelor in Arts)", "BCom(Bachelor in Commerce)",
            "BSc(Bachelor in Science)", "MA(Masters in Arts)",
            "MCom(Masters in Commerce)", "MSc(Masters in Science)"
        );

        $degrees = array();
        foreach ($degreeData as $data)
        {
            array_push($degrees, array(
                "name"          => $data,
                "created_at"    => now(),
                "updated_at"    => now()
            ));
        }

        Degree::insert($degrees);

    }
}
