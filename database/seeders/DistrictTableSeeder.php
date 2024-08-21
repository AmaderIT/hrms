<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Division;
use Illuminate\Database\Seeder;

class DistrictTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $districtData = array(
            array(
                "name" => "Barguna",
                "division_id"   => 1
            ),
            array(
                "name" => "Barisal",
                "division_id"   => 1
            ),
            array(
                "name" => "Bhola",
                "division_id"   => 1
            ),
            array(
                "name" => "Jhalokati",
                "division_id"   => 1
            ),
            array(
                "name" => "Patuakhali",
                "division_id"   => 1
            ),
            array(
                "name" => "Pirojpur",
                "division_id"   => 1
            ),
            array(
                "name" => "Bandarban",
                "division_id"   => 7
            ),
            array(
                "name" => "Brahmanbaria",
                "division_id"   => 7
            ),
            array(
                "name" => "Chandpur",
                "division_id"   => 7
            ),
            array(
                "name" => "Chittagong",
                "division_id"   => 7
            ),
            array(
                "name" => "Comilla",
                "division_id"   => 7
            ),
            array(
                "name" => "Cox's Bazar",
                "division_id"   => 7
            ),
            array(
                "name" => "Feni",
                "division_id"   => 7
            ),
            array(
                "name" => "Khagrachhari",
                "division_id"   => 7
            ),
            array(
                "name" => "Lakshmipur",
                "division_id"   => 7
            ),
            array(
                "name" => "Noakhali",
                "division_id"   => 7
            ),
            array(
                "name" => "Rangamati",
                "division_id"   => 7
            ),
            array(
                "name" => "Dhaka",
                "division_id"   => 6
            ),
            array(
                "name" => "Faridpur",
                "division_id"   => 6
            ),
            array(
                "name" => "Gazipur",
                "division_id"   => 6
            ),
            array(
                "name" => "Kishoreganj",
                "division_id"   => 6
            ),
            array(
                "name" => "Madaripur",
                "division_id"   => 6
            ),
            array(
                "name" => "Manikganj",
                "division_id"   => 6
            ),
            array(
                "name" => "Munshiganj",
                "division_id"   => 6
            ),
            array(
                "name" => "Narayanganj",
                "division_id"   => 6
            ),
            array(
                "name" => "Narsingdi",
                "division_id"   => 6
            ),
            array(
                "name" => "Rajbari",
                "division_id"   => 6
            ),
            array(
                "name" => "Shariatpur",
                "division_id"   => 6
            ),
            array(
                "name" => "Tangail",
                "division_id"   => 6
            ),
            array(
                "name" => "Bagerhat",
                "division_id"   => 4
            ),
            array(
                "name" => "Chuadanga",
                "division_id"   => 4
            ),
            array(
                "name" => "Jessore",
                "division_id"   => 4
            ),
            array(
                "name" => "Jhenaidah",
                "division_id"   => 4
            ),
            array(
                "name" => "Khulna",
                "division_id"   => 4
            ),
            array(
                "name" => "Kushtia",
                "division_id"   => 4
            ),
            array(
                "name" => "Magura",
                "division_id"   => 4
            ),
            array(
                "name" => "Meherpur",
                "division_id"   => 4
            ),
            array(
                "name" => "Narail",
                "division_id"   => 4
            ),
            array(
                "name" => "Satkhira",
                "division_id"   => 4
            ),
            array(
                "name" => "Jamalpur",
                "division_id"   => 8
            ),
            array(
                "name" => "Mymensingh",
                "division_id"   => 8
            ),
            array(
                "name" => "Netrokona",
                "division_id"   => 8
            ),
            array(
                "name" => "Sherpur",
                "division_id"   => 8
            ),
            array(
                "name" => "Bogra",
                "division_id"   => 3
            ),
            array(
                "name" => "Joypurhat",
                "division_id"   => 3
            ),
            array(
                "name" => "Naogaon",
                "division_id"   => 3
            ),
            array(
                "name" => "Natore",
                "division_id"   => 3
            ),
            array(
                "name" => "Chapainawabganj",
                "division_id"   => 3
            ),
            array(
                "name" => "Pabna",
                "division_id"   => 3
            ),
            array(
                "name" => "Rajshahi",
                "division_id"   => 3
            ),
            array(
                "name" => "Sirajganj",
                "division_id"   => 3
            ),
            array(
                "name" => "Dinajpur",
                "division_id"   => 2
            ),
            array(
                "name" => "Gaibandha",
                "division_id"   => 2
            ),
            array(
                "name" => "Kurigram",
                "division_id"   => 2
            ),
            array(
                "name" => "Lalmonirhat",
                "division_id"   => 2
            ),
            array(
                "name" => "Nilphamari",
                "division_id"   => 2
            ),
            array(
                "name" => "Panchagarh",
                "division_id"   => 2
            ),
            array(
                "name" => "Rangpur",
                "division_id"   => 2
            ),
            array(
                "name" => "Thakurgaon",
                "division_id"   => 2
            ),
            array(
                "name" => "Habiganj",
                "division_id"   => 5
            ),
            array(
                "name" => "Moulvibazar",
                "division_id"   => 5
            ),
            array(
                "name" => "Sunamganj",
                "division_id"   => 5
            ),
            array(
                "name" => "Sylhet",
                "division_id"   => 5
            ),
            array(
                "name" => "Gopalganj",
                "division_id"   => 6
            )
        );

        $districts = array();
        foreach ($districtData as $data)
        {
            array_push($districts, $data);
        }

        District::insert($districts);
    }
}
