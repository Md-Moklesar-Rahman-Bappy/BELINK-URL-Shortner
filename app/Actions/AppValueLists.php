<?php

namespace App\Actions;

use App\LinkDomain;
use App\LinkGroup;
use App\LinkOverlay;
use App\TrackingPixel;
use Common\Auth\Permissions\Permission;
use Common\Core\Values\ValueLists;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Str;
use const App\Providers\WORKSPACED_RESOURCES;

class AppValueLists extends ValueLists
{
    public function overlays($params = [])
    {
        if ( ! isset($params['userId'])) {
            return collect([]);
        }

        app(Gate::class)->authorize('index', [LinkOverlay::class, $params['userId']]);

        return app(LinkOverlay::class)
            ->select(['id', 'name'])
            ->where('user_id', $params['userId'])
            ->get();
    }

    public function pixels($params = [])
    {
        if ( ! isset($params['userId'])) {
            return collect([]);
        }

        app(Gate::class)->authorize('index', [TrackingPixel::class, $params['userId']]);

        return app(TrackingPixel::class)
            ->select(['id', 'name'])
            ->where('user_id', $params['userId'])
            ->get();
    }

    public function groups($params = [])
    {
        if ( ! isset($params['userId'])) {
            return collect([]);
        }

        app(Gate::class)->authorize('index', [LinkGroup::class, $params['userId']]);

        return app(LinkGroup::class)
            ->select(['id', 'name'])
            ->where('user_id', $params['userId'])
            ->get();
    }

    public function workspacePermissions($params = [])
    {
        $filters = array_map(function($resource) {
            if ($resource === LinkDomain::class) {
                return 'custom_domains';
            } else {
                return Str::snake(Str::pluralStudly(class_basename($resource)));
            }
        }, WORKSPACED_RESOURCES);

        return app(Permission::class)
            ->where('type', 'workspace')
            ->orWhere(function(Builder $builder) use($filters) {
                $builder->where('type', 'sitewide')->whereIn('group', $filters);
            })
            // don't return restrictions for workspace permissions so they
            // are not show when creating workspace role from admin area
            ->get(['id', 'name', 'display_name', 'description', 'group', 'type'])
            ->map(function(Permission $permission) {
                $permission->description = str_replace('ALL', 'all workspace', $permission->description);
                $permission->description = str_replace('creating new', 'creating new workspace', $permission->description);
                return $permission;
            });
    }
}
