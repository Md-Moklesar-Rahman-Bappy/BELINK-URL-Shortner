<?php

namespace App\Policies;

use App\LinkDomain;

class LinkDomainPolicy extends WorkspacedResourcePolicy
{
    protected $resource = LinkDomain::class;

    protected $permissionName = 'custom_domains';
}
