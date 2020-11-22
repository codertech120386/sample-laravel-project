<?php

use Illuminate\Database\Seeder;

use App\Workspace;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Workspace::class, 100)->create();
    }
}
