<?php

namespace Database\Seeders;
use App\Models\LateDeduction;
use App\Models\Department;
use Illuminate\Database\Seeder;
class LateDeductionsSeeder extends Seeder
{
    private $lateDeductionData = [];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $departments = Department::pluck("id");
        foreach($departments as $department) {
            LateDeduction::firstOrCreate([
                'department_id' => $department,
                'type' => 'leave',
                'total_days' => 0,
                'deduction_day' => 0,
            ]);
        }
    }
}
