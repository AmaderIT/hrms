<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bankData = array(
            "Sonali Bank LTD", "Janata Bank LTD", "Agrani Bank LTD","Rupali Bank LTD", "BASIC Bank LTD",
            "AB Bank LTD","Bangladesh Commerce Bank LTD", "Bank Asia LTD","BRAC Bank LTD", "Dhaka Bank LTD",
            "Dutch Bangla Bank LTD", "Eastern Bank LTD", "IFIC Bank LTD", "Jamuna Bank LTD", "Meghna Bank LTD",
            "Mercantile Bank LTD","Midland Bank LTD", "Modhumoti Bank LTD", "Mutual Trust Bank LTD", "National Bank LTD",
            "NCC Bank LTD", "NRB Bank LTD", "NRB Commercial Bank LTD", "NRB Global Bank LTD", "One Bank LTD",
            "Prime Bank LTD", "Pubali Bank LTD", "Simanto Bank LTD", "South Bangla Agriculture and Commerce Bank LTD",
            "Southeast Bank LTD","Standard Bank LTD", "The City Bank LTD","The Farmers Bank LTD", "The Premier Bank LTD",
            "Trust Bank LTD", "United Commercial Bank LTD", "Uttara Bank LTD", "Islami Bank Bangladesh LTD",
            "Al-Arafah Islami Bank LTD", "Export Import Bank of Bangladesh LTD", "Social Islami Bank LTD",
            "Shahjalal islami Bank LTD", "First Security Islami Bank LTD", "Union Bank LTD", "ICB Islamic Bank LTD",
            "Bank Al-Falah","Citibank NA", "Commercial Bank of Ceylon", "Habib Bank LTD", "Standard Chartered Bank",
            "State Bank of India", "Woori Bank", "Bangladesh Development Bank LTD", "Bangladesh Krishi Bank", "Rajshahi Krishi Unnayan Bank",
            "Karmasangsthan Bank", "Probashi Kallyan Bank", "Palli Sanchay Bank", "Grameen Bank", "Ansar-VDP Unnayan Bank",
            "Bangladesh Samabaya Bank Ltd", "The Dhaka Mercantile co-operative Bank Ltd", "Progoti Co-operative Land Development Bank LTD",
            "Bangladesh Krishibank LTD", "Dutch-Bangla Bank LTD", "Meghna Bank Limited", "Standard Chartered Bank LTD", "City Bank LTD"
        );

        $banks = array();
        foreach ($bankData as $data)
        {
            array_push($banks, array(
                "name"          => $data,
                "created_at"    => now(),
                "updated_at"    => now()
            ));
        }

        Bank::insert($banks);
    }
}
