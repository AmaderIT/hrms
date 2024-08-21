<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [];

        $settingData = [
            "per_page"                      => 50,
            "attendance_count_start_time"   => "6am",
            "meal_request_end_time"   => "20:00:00",
            "whms_url"   => "https://whms.bysl.live",
            "attendance_count_start_hour"   => "5",
        ];

        foreach ($settingData as $key => $value) {
            array_push($settings, [
                "name"          => $key,
                "value"         => $value,
                "created_at"    => now()
            ]);
        }

        Setting::insert($settings);
    }
}
