<?php

namespace App\Policies;

use App\LinkGroup;
use App\User;
use Illuminate\Database\Eloquent\Model;

class LinkGroupPolicy extends WorkspacedResourcePolicy
{
    protected $resource = LinkGroup::class;

    public function show(User $user, Model $linkGroup, int $workspaceId = null)
    {
        return (!$workspaceId && $linkGroup->public) || parent::show($user, $linkGroup, $workspaceId);
    }
}
