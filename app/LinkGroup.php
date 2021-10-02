<?php

namespace App;

use App\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property boolean $rotator
 */
class LinkGroup extends Model
{
    use BelongsToWorkspace;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'public' => 'boolean',
        'rotator' => 'boolean',
    ];

    public function links() {
        return $this->belongsToMany(Link::class, 'link_group_link')
            ->withoutGlobalScope('workspaced');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function randomLink() {
        return $this->belongsToMany(Link::class, 'link_group_link')
            ->inRandomOrder()
            ->limit(1)
            ->withoutGlobalScope('workspaced');
    }
}
