<?php

namespace Database\Seeders;
use App\Models\Plan;

use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [['Pay As You Go','pay-as-you-go',5896186,1588071,1684637],
        ['Month to Month','month-to-month',5896187,1588072,1684639],
        ['Prepay Annual','annual-plan',5896188,1588073,1684642]];
        foreach ($plans as $plan) {
            Plan::firstorCreate([
                'name' => $plan[0],
                'product_handle' => $plan[1],
                'product_id' => $plan[2],
                'component_id' => $plan[3],
                'price_point_id' => $plan[4]
            ]);
        }
    }
}
