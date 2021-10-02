<?php

namespace App\Policies;

use App\User;
use App\LinkOverlay;
use Common\Core\Policies\BasePolicy;

class LinkOverlayPolicy extends WorkspacedResourcePolicy
{
    protected $resource = LinkOverlay::class;
}
