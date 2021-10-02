<?php

use App\Link;
use App\LinkClick;
use App\LinkGroup;
use App\User;
use App\Workspace;
use Illuminate\Database\Seeder;

class LocalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $testUser = app(User::class)->findAdmin();
        DB::beginTransaction();

        $workspaces = factory(Workspace::class, 5)->create([
            'owner_id' => $testUser->id,
        ]);

        $links = factory(Link::class, 100)->create([
            'user_id' => $testUser->id,
            'workspace_id' => $workspaces->random()->id,
        ]);

        factory(LinkClick::class, 100)->create([
            'link_id' => $links->slice(0, 15)->random()->id,
        ]);

        factory(LinkGroup::class, 100)->create([
            'user_id' => $testUser->id,
        ])->each(function(LinkGroup $group) use($links) {
            $group->links()->attach($links->random(50));
        });

        DB::commit();
    }
}
