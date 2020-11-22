<?php

use App\WorkspaceBanner;
use Illuminate\Database\Seeder;

class WorkspaceBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(WorkspaceBanner::class, 500)->create();
    }
}
