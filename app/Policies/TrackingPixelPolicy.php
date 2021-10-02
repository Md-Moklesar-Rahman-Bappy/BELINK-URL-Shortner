<?php

namespace App\Policies;

use App\TrackingPixel;

class TrackingPixelPolicy extends WorkspacedResourcePolicy
{
    protected $resource = TrackingPixel::class;
}
