<?php

namespace App\Actions\Link;

use App\Link;
use Arr;
use Auth;
use Common\Core\HttpClient;
use Common\Tags\Tag;
use Exception;
use Str;
use Symfony\Component\DomCrawler\Crawler;

class CrupdateLink
{
    /**
     * @var Link
     */
    private $link;

    /**
     * @var HttpClient
     */
    private $http;

    public function __construct(Link $link)
    {
        $this->link = $link;
        $this->http = new HttpClient(['verify' => false]);
    }

    public function execute(Link $link, array $data): Link
    {
        $attributes = !$link->exists ?
            array_merge($this->getMetadataFromUrl($data['long_url']), ['hash' => Str::random(5)]) :
            [];

        $domainId = Arr::get($data, 'domain_id');
        $attributes = array_merge($attributes, [
            'long_url' => $data['long_url'],
            'expires_at' => $data['expires_at'] ?? null,
            'disabled' => $data['disabled'] ?? false,
            'type' => $data['type'] ?? 'direct',
            'type_id' => $data['type_id'] ?? null,
            'user_id' => $data['user_id'] ?? Auth::id(),
            'domain_id' => is_integer($domainId) ? $domainId : null, // can be 0
            'alias' => $data['alias'] ?? null,
            'title' => $data['title'] ?? Arr::get($attributes, 'title'),
            'description' => $data['description'] ?? Arr::get($attributes, 'description'),
        ]);

        // restore link if user has removed expires_at date from expired link
        if (is_null($attributes['expires_at'])) {
            $attributes['deleted_at'] = null;
        }

        // make sure not to clear password if it was not changed
        if (Arr::has($data, 'password')) {
            $attributes['password'] = $data['password'] ?: null;
        }

        $link->fill($attributes)->save();

        if ($rules = Arr::get($data, 'rules')) {
            $link->rules()->delete();
            $rules = $link->rules()->createMany($rules);
            $link->setRelation('rules', $rules);
        }

        if ($tagNames = Arr::get($data, 'tags')) {
            $tags = collect($tagNames)->map(function($name) {
                return ['name' => $name, 'type' => Tag::DEFAULT_TYPE];
            });
            $tags = app(Tag::class)->insertOrRetrieve($tags);
            $link->tags()->sync($tags);
            $link->setRelation('tags', $tags);
        }

        if ($pixels = Arr::get($data, 'pixels')) {
            $link->pixels()->sync($pixels);
        }

        if ($groups = Arr::get($data, 'groups')) {
            $link->groups()->sync($groups);
        }

        return $link;
    }

    private function getMetadataFromUrl($url)
    {
        $default = ['title' => null, 'description' => null, 'image' => null];

        // in case url is not reachable
        try {
            $content = $this->http->get($url);
        } catch (Exception $e) {
            return $default;
        }

        // if JSON response was returned
        if (is_array($content)) {
            return $default;
        }

        $crawler = new Crawler($content);
        $title = head($crawler->filter('title')->extract(['_text'])) ?: head($crawler->filter('meta[property="og:title"]')->extract(['content']));
        $description = head($crawler->filter('meta[name="description"]')->extract(['content'])) ?: head($crawler->filter('meta[property="og:description"]')->extract(['content']));
        $image = head($crawler->filter('meta[property="og:image"]')->extract(['content']));

        return [
            'title' => $title ? Str::limit($title, 100) : null,
            'description' => $description ? Str::limit($description, 180) : null,
            'image' => $image,
        ];
    }
}
