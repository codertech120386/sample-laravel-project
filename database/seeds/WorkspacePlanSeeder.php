<?php

use Illuminate\Database\Seeder;

use App\WorkspacePlan;

class WorkspacePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(WorkspacePlan::class, 500)->create();
    }
}
