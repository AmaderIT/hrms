<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $branchData = array(
            "Agrabad", "Amirabad Lohagara", "Ashugonj", "Ashulia", "Auliapur", "Bagerhat", "Banani",
            "Bandarban", "Bangabondhu Cantt.", "Barisal", "Bashundhara", "Beanibazar", "Bhaderganj",
            "Bhairab", "Bir Uttam Shaheed Mahboob Cantt.", "Bogra Cantt.", "CDA Avenue", "Centennial",
            "Chandpur" , "Chapainawabganj", "Chittagong Cantt.", "Chowmohoni", "Comilla", "Comilla Cantt.",
            "Companygonj", "Cox's Bazar", "Dashuria", "Daulatpur", "Dayarampur", "Dewan Bazar", "Dhamrai",
            "Dhanmondi Corporate", "Dholaikhal SME Service Center", "Dilkusha Corporate", "Elephant Road",
            "Faridpur", "Feni", "Gazipur Cantonment", "Goalabazar", "Golapganj", "Gopalgonj", "Gulshan Corporate",
            "Habigonj", "Halishahar", "Head Office", "Jahanabad Cantt", "Jalalabad Cantt.", "Jessore Cantt.",
            "Jhenaidah", "Joydevpur", "Joypara", "Jubilee Road", "Kabirhat", "Kadamtali", "Kafrul", "Kakrail",
            "Kanchpur", "Karwan Bazar", "Keraniganj", "Khagrachari", "Khatunganj", "Khawja Garib Newaz Avenue",
            "Khulna", "Kishorganj", "Kushtia", "Lalmonirhat", "Madhabdi SME/Krishi", "Madina Market", "Manikgonj SME/Krishi",
            "Matuail", "Millennium Corporate", "Mirerbazar SME/Krishi", "Mirpur", "Mirpur DOHS", "Mohakhali", "Mongla",
            "Moulvibazar", "Munshiganj", "Mymensingh Cantt.", "Narayangonj", "Narsingdi", "Natore SME/Krishi", "Naval Base",
            "Pangsha", "Patuatuly", "Payra Port", "Principal", "Radisson Blu Chattogram Bay View Hotel", "Rajendrapur Cantt.",
            "Rajshahi", "Ramu", "Rangamati", "Rangpur Cantt.", "Saidpur", "Savar Cantt.", "Shaheed Salahuddin Cantonment",
            "Shahjalal Uposhahor", "Shambugonj", "Shatibari", "Sheikh Hasina Cantonment", "Sherpur", "SKB", "Sreenagar", "Sunamgonj",
            "Sylhet Corporate", "Takerhat", "Tamai SME/Krishi", "Taranagar", "Titas", "Tongi", "Uttara Corporate"
        );

        $branches = array();
        foreach ($branchData as $data)
        {
            array_push($branches, array(
                "name"          => $data,
                "created_at"    => now(),
                "updated_at"    => now()
            ));
        }

        Branch::insert($branches);
    }
}
