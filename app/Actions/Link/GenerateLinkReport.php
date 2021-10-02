<?php

namespace App\Actions\Link;

use App\Link;
use App\LinkClick;
use App\LinkGroup;
use App\User;
use App\Workspace;
use Arr;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Common\Core\Values\ValueLists;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class GenerateLinkReport
{
    /**
     * @var CarbonPeriod
     */
    private $range;

    /**
     * @var Builder
     */
    private $query;

    /**
     * @var LinkClick
     */
    private $linkClick;

    /**
     * @param LinkClick $linkClick
     */
    public function __construct(LinkClick $linkClick)
    {
        $this->linkClick = $linkClick;
    }

    /**
     * @param array $params
     * @param Link|LinkGroup|User $model
     * @return array
     */
    public function execute($params, $model = null)
    {
        if (is_a($model, Link::class)) {
            $this->query = $model->clicks();
        } else if (is_a($model, LinkGroup::class)) {
            $this->query = $this->groupClicksQuery($model);
        } else if (is_a($model, User::class)) {
            $this->query = $this->allUserLinksQuery($model);
        } else if (is_a($model, Workspace::class)) {
           $this->query = $model->linkClicks();
        } else {
            $this->query = $this->linkClick->newQuery();
        }

        $clicks = $this->getClickData($params);
        $totalClicks = $clicks->sum('count');
        return [
            'clicks' => $clicks,
            'totalClicks' => $totalClicks,
            'devices' => $this->getData('device'),
            'browsers' => $this->getData('browser'),
            'platforms' => $this->getData('platform'),
            'locations' => $this->getLocationData($totalClicks),
            'referrers' => $this->getData('referrer'),
            'startDate' => $this->range->getStartDate()->toJSON(),
            'endDate' => $this->range->getStartDate()->toJSON(),
        ];
    }

    /**
     * @param int $totalClicks
     * @return Collection
     */
    private function getLocationData($totalClicks)
    {
        $locations = $this->getData('location');
        $countries = app(ValueLists::class)->countries();
        return $locations->map(function($location) use($countries, $totalClicks) {
            // only short country code is stored in DB, get and return full country name as well
            $location['code'] = strtolower($location['label']);
            $location['label'] = Arr::first($countries, function($country) use($location) {
                return $country['code'] === $location['code'];
            })['name'];
            // add percentage of total for each country
            $location['percentage'] = round((100 * $location['count']) / $totalClicks, 1);
            return $location;
        });
    }

    /**
     * @param array $params
     * @return Collection
     */
    private function getClickData($params)
    {
        $range = Arr::get($params, 'range', 'weekly');

        if ($range === 'custom' && $customRange = Arr::get($params, 'customRange')) {
            [$start, $end] = explode(':', $customRange);
            $this->range = CarbonPeriod::create(Carbon::parse($start), Carbon::parse($end));
            $clicks = $this->getData(DB::raw("DAY(link_clicks.created_at) as label"));

            foreach ($this->range as $date) {
                $this->maybePushLabel($clicks, $date->format('Y-m-d'));
            }

            $clicks = $clicks->sortBy('label');
        } else if ($range === 'yearly') {
            $this->range = CarbonPeriod::create(Carbon::now()->startOfYear(), '1 month', Carbon::now()->endOfYear());
            $clicks = $this->getData(DB::raw("MONTH(link_clicks.created_at) as label"));

            foreach ($this->range as $date) {
                $this->maybePushLabel($clicks, $date->month);
            }

            // sort by month and format label to "Jan"
            $clicks = $clicks->sortBy('label')
                ->map(function($click) {
                    $click['label'] = Carbon::createFromFormat('m', $click['label'])->shortLocaleMonth;
                    return $click;
                });

        } else if ($range === 'monthly') {
            $this->range = CarbonPeriod::create(Carbon::now()->startOfMonth()->startOfDay(), Carbon::now()->endOfMonth()->endOfDay());
            $clicks = $this->getData(DB::raw("DAY(link_clicks.created_at) as label"));

            foreach ($this->range as $date) {
                $this->maybePushLabel($clicks, $date->day);
            }

            // sort by day and format date to "01"
            $clicks = $clicks->sortBy('label')
                ->map(function($click) {
                    $click['label'] = Carbon::createFromFormat('d', $click['label'])->format('d');
                    return $click;
                });
        } else if ($range === 'hourly') {
            $this->range = CarbonPeriod::create(Carbon::now()->startOfDay(), '1 hour', Carbon::now()->endOfDay());
            $clicks = $this->getData(DB::raw("HOUR(link_clicks.created_at) as label"));

            foreach ($this->range as $date) {
                $this->maybePushLabel($clicks, $date->hour);
            }

            // sort by hour and format date to "24:00"
            $clicks = $clicks->sortBy('label')
                ->map(function($click) {
                    $click['label'] = Carbon::createFromFormat('H', $click['label'])->format('H:i');
                    return $click;
                });
        } else {
            $this->range = CarbonPeriod::create(Carbon::now()->startOfWeek()->startOfDay(), Carbon::now()->endOfWeek()->endOfDay());
            $clicks = $this->getData(DB::raw("DAY(link_clicks.created_at) as label"));

            foreach ($this->range as $date) {
                $this->maybePushLabel($clicks, $date->day);
            }

            // sort by day and format date to "Wen, 05"
            $clicks = $clicks->sortBy('label')
                ->map(function($click) {
                    $click['label'] = Carbon::createFromFormat('d', $click['label'])->format('d, D');
                    return $click;
                });
        }

        return $clicks->values();
    }

    private function maybePushLabel(Collection $clicks, $label)
    {
        $contains = $clicks->first(function($click) use($label) {
            return $click['label'] === $label;
        });
        if ( ! $contains) {
            $clicks->push([
                'label' => $label,
                'count' => 0
            ]);
        }
    }

    private function getData($select)
    {
        return $this->query
            ->where('crawler', false)
            ->whereBetween(
                'link_clicks.created_at',
                [$this->range->getStartDate(), $this->range->getEndDate()]
            )
            ->select([
                is_string($select) ? "$select as label" : $select,
                DB::raw('COUNT(*) as count')
            ])
            ->groupBy('label')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }

    private function allUserLinksQuery(User $user)
    {
        $ids = app(Link::class)->where('user_id', $user->id)->limit(500)->pluck('id');
        return $this->linkClick->whereIn('link_id', $ids);
    }

    private function groupClicksQuery(LinkGroup $group)
    {
        $ids = $group->links()->pluck('links.id');
        return $this->linkClick->whereIn('link_id', $ids);
    }
}
