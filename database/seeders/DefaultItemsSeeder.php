<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DefaultItem;

class DefaultItemsSeeder extends Seeder
{
    public function run()
    {
        $defaultItems = [
            ['Car Park 8 Ft Roof Height', 1700, 2406, 'Sq.Ft'],
            ['1st Floor', 2050, 2406, 'Sq.Ft'],
            ['2nd Floor', 2050, 2406, 'Sq.Ft'],
            ['3rd Floor', 2050, 2406, 'Sq.Ft'],
            ['Head Room 8 ft/ Lift Room', 1850, 450, 'Sq.Ft'],
            ['Elevation Work', 200000, 1, 'Lumpsum'],
            ['Sump R C C', 23, 12000, 'Cu.Ft'],
            ['Specktic Tank', 18, 12000, 'Cu.Ft'],
            ['Water Tank R C C', 23, 6000, 'Cu.Ft'],
            ['Water Tank staircase Grill', 15000, 1, 'Nos'],
            ['E.B. DB Panel', 15000, 10, 'Nos'],
            ['Weathering Tiles', 160, 2406, 'Sq.Ft'],
            ['Safety Gate', 135000, 1, 'Nos'],
            ['Lift 6 Passenger', 750000, 1, 'Nos'],
            ['Compound Gate', 80000, 2, 'Nos'],
            ['Compound Wall 8 ft', 1800, 209, 'R.ft'],
        ];

        foreach ($defaultItems as $item) {
            \App\Models\DefaultItem::create([
                'particular' => $item[0],
                'rate'       => $item[1],
                'sqFt'       => $item[2],
                'unit'       => $item[3],
            ]);
        }

        $this->command->info('✅ Default items inserted successfully!');
    }
}
