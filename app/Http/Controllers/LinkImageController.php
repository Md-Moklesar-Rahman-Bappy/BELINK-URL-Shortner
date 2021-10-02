<?php

namespace App\Http\Controllers;

use App\Link;
use Arr;
use Carbon\Carbon;
use Common\Core\BaseController;
use Common\Core\HttpClient;
use GuzzleHttp\Exception\ConnectException;
use Storage;

class LinkImageController extends BaseController
{
    /**
     * @var Link
     */
    private $link;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @param Link $link
     */
    public function __construct(Link $link)
    {
        $this->link = $link;
        $this->httpClient = new HttpClient(['timeout' => 5]);
    }

    /**
     * @param string $hash
     * @return string
     */
    public function show($hash)
    {
        $link = $this->link->where('hash', $hash)->firstOrFail();
        $this->authorize('show', $link);

        // Don't create image if request is coming from a crawler
        if (defined('SHOULD_PRERENDER')) {
            return $link->image;
        }

        $path = "link_images/{$link->hash}.jpg";

        if ( ! $link->thumbnail) {
            $createdImage = $this->createImage($link, $path);
            $link->fill(['thumbnail' => $createdImage ? $path : null])->save();

        // update every week
        } else if ($link->updated_at->lessThan(Carbon::now()->subWeek()))  {
            $createdImage = $this->createImage($link, $path);
            $link->fill(['thumbnail' => $createdImage ? $path : null])->save();
        }

        return $link->thumbnail ? Storage::disk('public')->get($link->thumbnail) : $link->image;
    }

    private function createImage(Link $link, $path)
    {
        try {
            $apis = [
                "https://s.wordpress.com/mshots/v1/$link->long_url?w=800",
                "https://api.pagepeeker.com/v2/thumbs.php?size=l&url=$link->long_url",
                "https://api.miniature.io/?width=800&height=600&screen=1024&url=$link->long_url",
                "https://image.thum.io/get/width/600/crop/900/".urldecode($link->long_url)
            ];
            $imageData = $this->httpClient->get(Arr::random($apis));
        } catch (ConnectException $e) {
            return false;
        }

        if (isset($imageData)) {
            Storage::disk('public')->put($path, $imageData);
            return true;
        }
    }
}
