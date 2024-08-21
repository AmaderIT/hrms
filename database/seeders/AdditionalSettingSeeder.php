<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AdditionalSettingSeeder extends Seeder
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
            "short_day_leave_count_in_hr"   => 2,
            "half_day_leave_count_in_hr"   => 5,
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
