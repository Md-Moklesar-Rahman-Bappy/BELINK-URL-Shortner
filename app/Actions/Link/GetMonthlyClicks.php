<?php

namespace App\Actions\Link;

use App\User;
use App\Workspace;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class GetMonthlyClicks
{
    /**
     * @param User|Workspace $model
     * @return int
     */
    public function execute($model)
    {
        $range = CarbonPeriod::create(
            Carbon::now()->startOfMonth(),
            '1 month',
            Carbon::now()->endOfMonth()
        );

        return $model->linkClicks()
            ->where('crawler', false)
            ->whereBetween(
                'link_clicks.created_at',
                [$range->getStartDate(), $range->getEndDate()]
            )->count();
    }
}
