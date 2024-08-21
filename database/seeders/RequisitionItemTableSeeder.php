<?php

namespace Database\Seeders;

use App\Models\RequisitionItem;
use Illuminate\Database\Seeder;

class RequisitionItemTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            "Napkin Tissue",
            "Facial Tissue",
            "Marker",
            "Hand Sanitizer",
            "Pen",
            "Notebook",
        ];

        foreach ($items as $item) {
            RequisitionItem::create(["name"  => $item]);
        }
    }
}
