<?php

namespace App\Policies;

use App\LinkPage;

class LinkPagePolicy extends WorkspacedResourcePolicy
{
    protected $resource = LinkPage::class;
}
