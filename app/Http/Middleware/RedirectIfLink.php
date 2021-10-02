<?php

namespace App\Http\Middleware;

use App\Actions\Link\GetMonthlyClicks;
use App\Actions\Link\LogLinkClick;
use App\Exceptions\LinkRedirectFailed;
use App\Link;
use App\LinkClick;
use App\LinkGroup;
use App\LinkRule;
use App\Notifications\ClickQuotaExhausted;
use Closure;
use Common\Core\AppUrl;
use Common\Core\Prerender\HandlesSeo;
use Common\Domains\CustomDomain;
use Common\Settings\Settings;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Str;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectIfLink
{
    use HandlesSeo;

    const CLIENT_ROUTES = [
        'dashboard',
        'link-groups',
        'admin',
        'billing',
        'workspace',
        'contact',
        'update',
        'pages'
    ];

    /**
     * @var Link
     */
    private $link;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var CustomDomain
     */
    private $customDomain;

    /**
     * @var AppUrl
     */
    private $appUrl;

    public function __construct(Link $link, Settings $settings, CustomDomain $customDomain, AppUrl $appUrl)
    {
        $this->link = $link;
        $this->settings = $settings;
        $this->customDomain = $customDomain;
        $this->appUrl = $appUrl;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $path = $this->getPath($request);

        if ( ! $this->isSystemRoute($path)) {
            if ($link = $this->findLink($path)) {
                $response = $this->handleLink($link, $request);
            } else if ($linkGroup = $this->findLinkGroup($path)) {
                if ( ! $linkGroup->rotator) {
                    $response = $this->handleLinkGroup($linkGroup, $request);
                } else if ($rotatorLink = $linkGroup->randomLink()->first()) {
                    $response = $this->handleLink($rotatorLink, $request);
                }
            }
        }

        return $response ?? $next($request);
    }

    private function findLink($hash): ?Link
    {
        return $this->link
            ->withoutGlobalScope('workspaced')
            ->with('pixels')
            ->where('hash', $hash)
            ->orWhere('alias', $hash)
            ->first();
    }

    private function findLinkGroup($hash): ?LinkGroup
    {
        return app(LinkGroup::class)
            ->withoutGlobalScope('workspaced')
            ->where('hash', $hash)
            ->where('public', true)
            ->first();
    }

    private function handleLinkGroup(LinkGroup $linkGroup, Request $request)
    {
        $linkGroupResponse = ['linkGroup' => $linkGroup->load('links')];
        $prerenderResponse = $this->handleSeo($linkGroupResponse, [
            'prerender' => [
                'config' => 'link-group.show',
                'view' => 'link-group.show',
            ]
        ]);
        if (defined('SHOULD_PRERENDER')) {
            return $prerenderResponse;
        } else {
            // need to cast to array when returning data to client
            // as laravel does not cast it correctly for some reason
            $linkGroupResponse['seo'] = $linkGroupResponse['seo']->toArray();
            $request->route()->setParameter('linkGroupResponse', $linkGroupResponse);
        }
    }

    private function handleLink(Link $link, Request $request)
    {
        if ($link && $link->expires_at && $link->expires_at->lessThan(now())) {
            throw (new LinkRedirectFailed("Link is past its expiration date ($link->expires_at)"))
                ->setLink($link);
        }

        if ($link && $this->isValidDomain($link) && !$this->overClickQuota($link)) {
            // redirect to stats page
            if (Str::endsWith($request->getUri(), '+')) {
                return redirect(url("dashboard/links/{$link->id}"));
            }

            // need to call this after link has already been loaded
            $link->load('custom_page');
            $click = app(LogLinkClick::class)->execute($link);
            $link = $this->applyRules($link, $click);

            // set seo meta tags on link response
            $linkResponse = ['link' => $link];
            $linkPrerenderResponse = $this->handleSeo($linkResponse, [
                'prerender' => [
                    'config' => 'link.show',
                    'view' => 'link.show',
                ]
            ]);

            // set link on route so it can be used in blade redirect templates and frontend
            $request->route()->setParameter('linkResponse', $linkResponse);

            if ($link->type === 'direct' && !$link->password) {
                $redirectHeaders =  ['Cache-Control' => 'no-cache, no-store', 'Expires' => -1];
                // redirect to long url instantly if link type is "direct"
                if ($link->pixels->isEmpty()) {
                    config()->set('session.driver', 'array');
                    return new RedirectResponse($link->long_url, 301, $redirectHeaders);
                    // will need to show pixels before redirecting
                } else {
                    return response()->view('redirects.direct', [], 301, $redirectHeaders);
                }
            }

            // pre-render links for crawlers
            if (isset($linkResponse)) {
                if (defined('SHOULD_PRERENDER')) {
                    return $linkPrerenderResponse;
                } else {
                    // need to cast to array when returning data to client
                    // as laravel does not cast it correctly for some reason
                    $linkResponse['seo'] = $linkResponse['seo']->toArray();
                    $request->route()->setParameter('linkResponse', $linkResponse);
                }
            }

            // other link types will be handled by frontend so
            // we can just continue with booting app normally
        }
    }

    private function isValidDomain(Link $link)
    {
        if ( ! $defaultHost = $this->settings->get('custom_domains.default_host')) {
            $defaultHost = config('app.url');
        }
        $defaultHost = $this->appUrl->getHostFrom($defaultHost);
        $requestHost = $this->appUrl->getRequestHost();

        // link should only be accessible via single domain
        if ($link->domain_id > 0) {
            $domain = $this->customDomain->forUser($link->user_id)->find($link->domain_id);
            if ( ! $domain || ! $this->appUrl->requestHostMatches($domain->host)) {
                throw (new LinkRedirectFailed("Link is set to only be accessible via '{$domain->host}', but request domain is '$requestHost'"))
                    ->setLink($link);
            }
        }

        // link should be accessible via default domain only
        else if ($link->domain_id === 0) {
            if ( ! $this->appUrl->requestHostMatches($defaultHost)) {
                throw (new LinkRedirectFailed("Link is set to only be accessible via '$defaultHost' (default domain), but request domain is '$requestHost'"))
                    ->setLink($link);
            }
        }

        // link should be accessible via default and all user connected domains
        else {
            if ($this->appUrl->requestHostMatches($defaultHost)) return true;
            $domains = $this->customDomain->forUser($link->user_id)->get();
            $customDomainMatches = $domains->contains(function(CustomDomain $domain) {
                return $this->appUrl->requestHostMatches($domain->host);
            });
            if ( ! $customDomainMatches) {
                throw (new LinkRedirectFailed("Current domain '$requestHost' does not match default domain or any custom domains connected by user."))
                    ->setLink($link);
            }
        }

        return true;
    }

    /**
     * @param Link $link
     * @param LinkClick $click
     * @return Link
     */
    private function applyRules(Link $link, LinkClick $click)
    {
        // only apply the first matching rule
        $first = $link->rules->first(function(LinkRule $rule) use($click) {
            if ($rule->type === 'geo' && $this->settings->get('links.geo_targeting')) {
                return $click->location === $rule->key;
            } else if ($rule->type === 'device' && $this->settings->get('links.device_targeting')) {
                return $click->device === $rule->key;
            } else {
                return false;
            }
        });

        if ($first) {
            $link->long_url = $first->value;
        }

        return $link;
    }

    private function isSystemRoute($path)
    {
        return Str::contains($path, '/') || array_search($path, self::CLIENT_ROUTES) !== false;
    }

    private function getPath(Request $request)
    {
        // if original url is specified, get path from that url
        // this allows testing locally via bootstrap-data url
        if ($original = $request->get('original_url')) {
            $path = parse_url($original)['path'];
        } else {
            $path = parse_url($request->getUri())['path'];
        }

        $path = str_replace('/public/', '', $path);
        $path = ltrim($path, '/');
        return rtrim($path, '+');
    }

    private function overClickQuota(Link $link)
    {
        return false;
        // link might not be attached to user
        if ( ! $link->user) {
            return false;
        }
        $quota = $link->user->getRestrictionValue('links.create', 'click_count');
        if (is_null($quota)) return false;

        $totalClicks = app(GetMonthlyClicks::class)->execute($link->user);

        if ($quota < $totalClicks) {
            $alreadyNotifiedThisMonth = app(DatabaseNotification::class)
                ->where('type', ClickQuotaExhausted::class)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->exists();
            if ( ! $alreadyNotifiedThisMonth) {
                $link->user->notify(new ClickQuotaExhausted());
            }
            throw (new LinkRedirectFailed('User or workspace this link belongs to is over their click quota for the month.'))
                ->setLink($link);
        }

        return false;
    }
}
