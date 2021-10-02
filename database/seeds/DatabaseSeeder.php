<?php

use App\Link;
use App\LinkClick;
use App\LinkGroup;
use App\User;
use App\Workspace;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(BillingPlanSeeder::class);
        $this->call(WorkspaceRoleSeeder::class);
    }
}
