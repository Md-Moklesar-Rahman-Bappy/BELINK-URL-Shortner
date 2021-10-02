<?php

namespace App\Actions\Link;

use App\Link;
use Arr;
use Common\Database\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Str;

class PaginateLinks
{
    /**
     * @var Link
     */
    private $link;

    /**
     * @param Link $link
     */
    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function execute($params)
    {
        $paginator = new Paginator($this->link, $params);
        $paginator->filterColumns = ['password', 'status' => function(Builder $query, $status) {
            if ($status === 'expired') {
                $query->onlyTrashed();
            } else if ($status === 'disabled') {
                $query->where('disabled', true);
            } else if ($status === 'enabled') {
                $query->where('disabled', false);
            }
        }, 'expires_at', 'type'];
        $paginator->with(['rules', 'tags', 'pixels', 'domain', 'user', 'groups']);
        $paginator->withCount(['clicks' => function(Builder $query) {
            return $query->where('crawler', false);
        }]);
        $paginator->query()->withTrashed();

        $paginator->searchCallback = function(Builder $builder, $query) {
            return $builder->where('long_url', 'like', "%$query%")
                ->orWhere('hash', 'like', "%$query%");
        };

        if ($groupId = Arr::get($params, 'groupId')) {
            // get only links that either belong to specified group or belong to any group besides it
            $operator = Str::contains($groupId, '!') ? '<' : '>=';
            $groupId = str_replace('!', '', $groupId);
            $paginator->query()->whereHas('groups', function(Builder $builder) use($groupId) {
                $builder->where('link_group_id', $groupId);
            }, $operator);
        }

        if ($userId = $paginator->param('userId')) {
            $paginator->where('user_id', $userId);
        }

        return $paginator->paginate();
    }
}
