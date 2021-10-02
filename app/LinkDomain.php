<?php

namespace App;

use App\Traits\BelongsToWorkspace;
use Common\Domains\CustomDomain;

class LinkDomain extends CustomDomain
{
    use BelongsToWorkspace;

    protected $table = 'custom_domains';
}
