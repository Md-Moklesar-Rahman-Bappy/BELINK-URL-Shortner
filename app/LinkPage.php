<?php

namespace App;

use App\Traits\BelongsToWorkspace;
use Common\Pages\CustomPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LinkPage extends CustomPage
{
    use BelongsToWorkspace {
        booted as protected workspaceScope;
    }

    public $table = 'custom_pages';
    const PAGE_TYPE = 'link_page';

    protected static function booted()
    {
        static::addGlobalScope('linkPage', function (Builder $builder) {
            $builder->where('type', self::PAGE_TYPE);
        });

        static::creating(function (Model $builder) {
            $builder->type = self::PAGE_TYPE;
        });

        self::workspaceScope();
    }
}
