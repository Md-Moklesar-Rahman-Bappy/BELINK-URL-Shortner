<?php

namespace App\Policies;

use App\Link;

class LinkPolicy extends WorkspacedResourcePolicy
{
    protected $resource = Link::class;
}
