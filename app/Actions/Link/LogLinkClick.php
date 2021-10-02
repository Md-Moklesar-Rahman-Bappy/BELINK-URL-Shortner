<?php


namespace App\Actions\Link;

use App\Link;
use App\LinkClick;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Str;

class LogLinkClick
{
    /**
     * @var Link
     */
    private $link;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Agent
     */
    private $agent;

    /**
     * @param Link $link
     * @param Request $request
     * @param Agent $agent
     */
    public function __construct(Link $link, Request $request, Agent $agent)
    {
        $this->link = $link;
        $this->request = $request;
        $this->agent = $agent;
    }

    /**
     * @param Link $link
     * @return LinkClick
     */
    public function execute(Link $link)
    {
        if ($link) {
            return $click = $this->log($link);
        }
    }

    /**
     * @param Link $link
     * @return LinkClick
     */
    private function log(Link $link)
    {
        $referrer = $this->request->server('HTTP_REFERER');

        $attributes = [
            'link_type' => $link->type,
            'location' => $this->getLocation(),
            'ip' => $this->request->ip(),
            'platform' => strtolower($this->agent->platform()),
            'device' => $this->getDevice(),
            'crawler' => $this->agent->isRobot(),
            'browser' => strtolower($this->agent->browser()),
            // if referrer was any page from our site set referrer as null
            'referrer' => Str::contains($referrer, url('')) ? null : Str::limit($referrer, 190, ''),
        ];

        return $link->clicks()->create($attributes);
    }

    private function getDevice() {
        if ($this->agent->isMobile()) {
            return 'mobile';
        } else if ($this->agent->isTablet()) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    private function getLocation()
    {
        return strtolower(geoip($this->getIp())['iso_code']);
    }

    private function getIp(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return $this->request->ip();
    }
}
