<?php

namespace App;

use App\Traits\BelongsToWorkspace;
use Carbon\Carbon;
use Common\Domains\CustomDomain;
use Common\Pages\CustomPage;
use Common\Settings\Settings;
use Common\Tags\Tag;
use Eloquent;
use Hash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Arr;

/**
 * App\Link
 *
 * @property int $id
 * @property string $hash
 * @property string $alias
 * @property string $long_url
 * @property string|null $password
 * @property Carbon|null $expires_at
 * @property string|null $description
 * @property string|LinkOverlay|null $type
 * @property int|null $type_id
 * @property int $user_id
 * @property integer $domain_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|LinkClick[] $clicks
 * @property-read string $short_url
 * @property-read Collection|LinkRule[] $rules
 * @property-read Collection|Tag[] $tags
 * @property-read User $user
 * @property-read LinkOverlay|null $custom_page
 * @property-read TrackingPixel[]|Collection $pixels
 * @property-read CustomDomain $domain
 * @mixin Eloquent
 */
class Link extends Model
{
    use SoftDeletes, BelongsToWorkspace;

    protected $guarded = ['id'];
    protected $hidden = ['password'];
    protected $appends = ['short_url', 'has_password'];
    protected $attributes = ['type' => 'default'];
    protected $dates = ['expires_at'];

    protected $casts = [
        'id' => 'integer',
        'domain_id' => 'integer',
        'user_id' => 'integer',
        'disabled' => 'boolean',
        'has_password' => 'boolean',
    ];

    public function rules()
    {
        return $this->hasMany(LinkRule::class);
    }

    public function clicks() {
        return $this->hasMany(LinkClick::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function groups() {
        return $this->belongsToMany(LinkGroup::class, 'link_group_link')
            ->withoutGlobalScope('workspaced');
    }

    public function pixels() {
        return $this->belongsToMany(TrackingPixel::class, 'link_tracking_pixel')
            ->withoutGlobalScope('workspaced');
    }

    public function domain() {
        return $this->belongsTo(CustomDomain::class, 'domain_id')
            ->withoutGlobalScope('workspaced')
            ->select(['id', 'host']);
    }

    public function custom_page()
    {
        $namespace = $this->type === 'overlay' ?
            LinkOverlay::class :
            CustomPage::class;

        return $this->belongsTo($namespace, 'type_id')
            ->withoutGlobalScope('workspaced');
    }

    public function getHasPasswordAttribute()
    {
        return !!Arr::get($this->attributes, 'password');
    }

    public function getShortUrlAttribute()
    {
        if ($this->domain_id && $this->relationLoaded('domain') && $this->domain) {
            $defaultHost = $this->domain->host;
        } else {
            $defaultHost = app(Settings::class)->get('custom_domains.default_host') ?: config('app.url');
        }

        return $defaultHost . '/' . ($this->alias ?: $this->hash);
    }

    public function getLongUrlAttribute($value)
    {
        return parse_url($value, PHP_URL_SCHEME) === null ? "https://$value" : $value;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ? Hash::make($value) : null;
    }
}
