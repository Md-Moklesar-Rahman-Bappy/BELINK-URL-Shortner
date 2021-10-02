<?php

namespace App\Traits;

use App\ActiveWorkspace;
use Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToWorkspace
{
    protected static function booted()
    {
        static::addGlobalScope('workspaced', function (Builder $builder) {
            $activeWorkspace = app(ActiveWorkspace::class);
            $filteredByUser = false;

            // remove user_id where so we dont filter by both workspace id and user id
            if ( ! $activeWorkspace->personal()) {
                $query = $builder->getQuery();
                foreach ($query->wheres as $key => $where) {
                    if (Arr::get($where, 'column') === 'user_id') {
                        $filteredByUser = true;
                        $userId = $query->wheres[$key]['value'];
                        unset($query->wheres[$key]);
                        foreach ($query->bindings['where'] as $bindingKey => $binding) {
                            if ($binding === $userId) {
                                unset($query->bindings['where'][$bindingKey]);
                                break;
                            }
                        }
                        break;
                    }
                }
            }


            // if it's not personal workspace and we're not filtering by user id, we
            // can assume we're inside admin and should not add workspace filter
            if ($filteredByUser) {
                $builder->where('workspace_id', $activeWorkspace->workspace()->id ?? null);
            }
        });

        static::creating(function (Model $builder) {
            $activeWorkspace = app(ActiveWorkspace::class);
            $builder->workspace_id = $activeWorkspace->workspace()->id ?? null;
        });
    }
}
