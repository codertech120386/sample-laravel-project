<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        // $this->call(WorkspaceSeeder::class);
        $this->call(AmenitySeeder::class);
        $this->call(WorkspaceBannerSeeder::class);
        $this->call(WorkspaceImageSeeder::class);
        // $this->call(WorkspacePlanSeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(SubscriptionSeeder::class);
    }
}
